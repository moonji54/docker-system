<?php

namespace Drupal\nrgi_frontend\Plugin\PdfGenerator;

use Drupal\node\NodeInterface;
use Drupal\soapbox_pdf\Plugin\PdfGenerator\DefaultPdfGenerator;
use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Page;
use HeadlessChromium\Utils;

/**
 * Provides an NRGI specific PDF generator.
 *
 * @PdfGenerator(
 *   id = "nrgi_pdf",
 *   label = @Translation("NRGI PDF"),
 * )
 *
 * @property \Drupal\Core\Site\Settings $settings
 */
class NrgiPdfGenerator extends DefaultPdfGenerator {

  /**
   * Internal storage for browser object.
   *
   * @var \HeadlessChromium\Browser|null
   */
  private Browser | null $browser = NULL;

  /**
   * {@inheritdoc}
   */
  public function generateMainPdf(NodeInterface $node) {
    $this->generatePdf(
      $this->getNodePdfPage($node),
      $this->getTemporaryFilePath($node)
    );
  }

  /**
   * Generates PDF from web page with options.
   *
   * @param string $page_url
   *   Web page URL.
   * @param string $output_file_uri
   *   Destination file path.
   * @param array $options
   *   Generation options.
   */
  public function generatePdf(string $page_url, string $output_file_uri, array $options = []): void {
    if ($this->isDdev()) {
      $site_host = \Drupal::request()->getSchemeAndHttpHost();
      $settings = \Drupal::service('settings');
      $ngrok_host = $settings->get('ngrok_url');
      if ($ngrok_host) {
        // Use the ngrok URL when using DDEV Local.
        $page_url = str_replace($site_host, $ngrok_host, $page_url);
      }
      else {
        // Local developer help message.
        $message = t('Please see project README.md for how to set up ngrok for testing PDF generation locally in DDEV.');
        \Drupal::messenger()->addWarning($message);
      }
    }

    try {
      // Open new tab.
      $page = $this->getBrowser()->createPage();
      $page->setUserAgent('custom user agent');

      /*
       * Rewrite since this will be closed:
       * https://github.com/chrome-php/chrome/issues/41
       */
      // Here will be HTTP status code of request.
      $status_code = NULL;
      // Attach listener to event, when request response received.
      $page->getSession()->on('method:Network.responseReceived',
        function ($params) use (&$status_code, $page_url) {
          if ($params['type'] === 'Document'
              && $params['response']['url'] === $page_url) {
            // We care just of mainly loaded url, not other assets.
            $status_code = $params['response']['status'];
          }
        }
      );

      // Go to page url and wait for networkIdle event
      // (i.e. no network activity for last 500ms).
      $page->navigate($page_url)
        ->waitForNavigation(Page::NETWORK_IDLE);

      // Check if page loaded successfully.
      if ($status_code !== 200) {
        throw new \Exception("Failed to GET web page {$page_url} HTTP CODE {$status_code}.");
      }

      // Perform request to check if page has paged.js.
      $is_paged_req = $page->evaluate('typeof PagedPolyfill === "object"')
        ->waitForResponse();
      $is_paged = $is_paged_req->getReturnType() === 'boolean'
                  && $is_paged_req->getReturnValue();
      if ($is_paged) {
        // If page have paged.js, then wait until it fully renders the page.
        $callable = function () use ($page) {
          // Delay of each check attempt (in milliseconds).
          $delay = 500;
          while (TRUE) {
            // Run JS expression in browser to check renderer status.
            $response = $page->evaluate('PagedPolyfill.chunker.rendered')
              ->waitForResponse();
            $paged_rendered = $response->getReturnValue();
            if ($paged_rendered) {
              return TRUE;
            }
            yield $delay;
          }
        };
        // How long we will wait for paged.js to finish render (in seconds).
        $paged_wait = 60;
        // Run rendering checked function with helper method,
        // to limit max execution time.
        Utils::tryWithTimeout($paged_wait * 1000000, $callable());
      }

      // Generate a PDF of page.
      $options = array_merge($this->getDefaultPdfOptions(), $options);
      $pdf = $page->pdf($options);

      // Transfer file and save file locally
      // with operation timeout in 5 min (300000 milliseconds).
      $pdf->saveToFile($output_file_uri, 300000);
    }
    catch (\Exception $e) {
      // Necessarily log error.
      $this->getLogger('soapbox_pdf')
        ->error("PDF generation failed with exception: \"{$e->getMessage()}\".");
    } finally {
      try {
        if (isset($page) && $page instanceof Page) {
          $page->close();
        }
      }
      catch (CommunicationException) {
        // Do nothing with this, as we don"t care a lot about that.
      }
    }
  }

  /**
   * Get browser connection.
   *
   * @return \HeadlessChromium\Browser
   *   Browser instance.
   *
   * @throws \HeadlessChromium\Exception\BrowserConnectionFailed
   */
  public function getBrowser(): Browser {
    if (!$this->browser instanceof Browser) {
      $this->browser = BrowserFactory::connectToBrowser(
        $this->getBrowserSocketUri()
      );
    }
    return $this->browser;
  }

  /**
   * Get websocket uri to remote web browser.
   *
   * @return string
   *   Websocket uri string.
   */
  protected function getBrowserSocketUri(): string {
    // Use browserless.io cloud browser as fallback.
    $api_key = $this->settings->get('browserless_api_key');
    return 'wss://chrome.browserless.io/webdriver?token=' . $api_key;
  }

  /**
   * Check if current environment is local.
   *
   * @return bool
   *   True if we are on DDEV, false otherwise.
   */
  protected function isDdev(): bool {
    return getenv('IS_DDEV_PROJECT') == 'true';
  }

  /**
   * Get default options for PDF generation.
   *
   * @return array
   *   List of options.
   */
  public function getDefaultPdfOptions(): array {
    return [
      'landscape' => FALSE,
      'preferCSSPageSize' => TRUE,
      'marginTop' => 0,
      'marginBottom' => 0,
      'marginLeft' => 0,
      'marginRight' => 0,
      'displayHeaderFooter' => FALSE,
      'printBackground' => TRUE,
    ];
  }

  /**
   * Destructs PdfGenerator class.
   */
  public function __destruct() {
    // Cleanup: try close the working browser.
    if ($this->browser instanceof Browser) {
      try {
        $this->browser->close();
      }
      catch (\Exception) {
        // Do nothing with this, as we don"t care a lot about that.
      }
    }
  }

}

# Run this command pre-commit to ensure no dev tools are enabled.
setup-production:
	ddev exec drush pm-uninstall devel, stage_file_proxy -y

# Enable development modules.
setup-dev:
	ddev exec drush pm-enable devel, stage_file_proxy -y
	ddev exec drush config-set stage_file_proxy.settings origin "https://SBTODO.com" -y

# Remove git subrepos.
post-composer-pantheon:
	# Removing git subrepo git folders for Pantheon. If you have accidentally committed one,
	# Please use `git rm --cached path/to/folder -r`.
	find ./web -type d -name ".git" | xargs rm -rf
	find ./web -name ".gitignore" | xargs rm -rf
	find ./web -name ".gitmodules" | xargs rm -rf
	find ./vendor -type d -name ".git" | xargs rm -rf
	find ./vendor -name ".gitignore" | xargs rm -rf
	find ./vendor -name ".gitmodules" | xargs rm -rf

# Deployment to pantheon
pantheon-deploy:
	# repo sub reposls -la
	make post-composer-pantheon
	# push to pantheon
	git push pantheon
	# removing the soapbox modules (make sure that you are using the correct folder below) and then reinstall the modules with composer
	# so that the drupal module git repositories are preserved to make further update sto the modules when required
	rm -rf web/modules/soapbox && composer update sb-dev-team/drupal_modules

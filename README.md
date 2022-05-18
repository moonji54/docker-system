# Installation instructions

[See Wiki home](https://gitlab.com/sb-dev-team/white-label-drupal-9/-/wikis/Home)

# Layout builder Paragraphs configuration

1. Build the unique Layout column configurations in `nrgi_layout_builder/src/Plugin/Layout/*`,
eg One Column, Two Column, etc.
1. Build the unique Layouts the client can add `nrgi_layout_builder/nrgi_layout_builder.layouts.yml`
noting that you should reuse the One Column, Two Column, etc as the `class`. There
might be multiple Layouts pointing to the same class, eg "Call to action with video",
"Call to action with image".
1. Build the unique twig files for each layout column configuration in `nrgi_layout_builder/layouts/`.
1. Set up the appropriate CSS for the layouts in `nrgi_layout_builder/assets/css/layout-builder-grid.css`.
1. Add the available layouts to the Container Paragraph Type (Paragraph Type > Layout Paragraphs >
Available Layouts), eg, /admin/structure/paragraphs_type/container
1. Add the available layouts to the Container Paragraph Type (Paragraph Type > Layout Paragraphs >
Available Layouts), eg, /admin/structure/paragraphs_type/container
1. Select which Paragraph Types are allowed per column per Layout under
Manage Form Display of the components field that uses the `NRGI Layout Paragraphs` plugin, eg
/admin/structure/types/manage/article/form-display

# Simple Theme — Folder Gallery Custom Theme Example

`simple` is the built-in reference theme for **Folder Gallery**. It demonstrates how a theme receives folder and image data and renders a lightweight tabular gallery view using generated `small` image variants with a safe fallback to original files, without declaring custom theme settings.

## How Custom Themes Work

1. **Theme Discovery**
   Create a new subfolder inside your gallery themes directory (for example `themes/my-custom-theme/`) with a theme manifest/metadata file (`theme.json` or header comment) and a main render template file.
2. **Theme Settings (Optional)**
   If your theme needs configurable options, declare a `"settings"` schema object in `theme.json` (with field `type`, `label`, `description`, `default`, and constraints such as `min`, `max`, `step`, or `options`). Themes without `"settings"` (such as `simple`) need no settings code at all.
3. **Activation & Configuration**
   Open **Folder Gallery → Gallery Themes** in the WordPress admin to activate your theme. For themes that define `"settings"`, a **Settings** button and dynamic form automatically appear so values can be saved per theme.
4. **Rendering**
   When `[folder_gallery]` or `[folder_gallery path="subfolder"]` runs on the frontend, the plugin resolves the active theme, merges schema defaults with any saved options, passes them in `$gallery_data['theme_settings']`, and includes `template.php`.

## Available Template Data & Variables

Depending on how your theme template is invoked, check for the gallery data array/object passed into the template scope (for example `$gallery` / `$gallery_data`):

- **Theme & settings**
  - `theme` (`array`): Active theme metadata (`slug`, `name`, `version`).
  - `theme_settings` (`array`): Resolved settings values for the active theme based on `theme.json` defaults and saved options (empty array if the theme declares no `settings`).
- **Folder metadata**
  - `path` (`string`): Relative folder path inside `folder-gallery` (empty string for root).
  - `title` (`string`): Folder title from `manifest.json`.
  - `description` (`string`): Sanitized HTML folder description from `manifest.json`.
- **Folders list (`folders`)**
  - `name` (`string`): Subfolder name on disk.
  - `title` (`string`): Subfolder title from its `manifest.json`.
  - `description` (`string`): Subfolder description.
  - `path` (`string`): Relative path to the subfolder.
  - `image_count` (`int`): Number of original images in the subfolder.
- **Images list (`images`)**
  - `name` (`string`): Original image file name.
  - `title` (`string`): Image title from `manifest.json`.
  - `description` (`string`): Sanitized HTML image description from `manifest.json`.
  - `path` (`string`): Relative image path.
  - `url` (`string`): Full-size (original) image URL.
  - `filesize` (`int`): Original file size in bytes.
  - `variants` (`array`): Generated size info (`small` and `medium`), each containing:
    - `exists` (`bool`): Whether the variant file currently exists on disk.
    - `url` (`string`): Variant URL (including cache-busting `?v=` timestamp in admin/scanner).
    - `width` (`int`): Variant width in pixels.
    - `height` (`int`): Variant height in pixels.

## Best Practices for Custom Themes

- **Always use a fallback to `url`:** Variants (`-fg-small`, `-fg-medium`) are generated based on configured max heights and may be missing on older or manually edited folders. Always fall back to `$image['url']` if a variant URL is empty or `exists` is false.
- **Provide template fallbacks for `theme_settings`:** Always read `$gallery_data['theme_settings']` safely with fallback defaults in `template.php` in case the template is rendered standalone or a setting is omitted.
- **Scale by height cleanly:** Generated variants are scaled to a target max height without upscaling smaller originals; style containers with `width: auto; height: auto;` or `object-fit` so aspect ratios stay natural.
- **Escape output properly:** Use `esc_url()` for image/link URLs, `esc_attr()` for attributes, `esc_html()` for plain text/titles, and `wp_kses_post()` for HTML descriptions.
- **Scope your CSS:** Prefix all CSS classes (for example `.fg-mytheme-...`) so theme styles do not leak into the WordPress theme or other galleries on the page.
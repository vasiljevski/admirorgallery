# Masonry Theme — Folder Gallery

Responsive Masonry grid theme for **Folder Gallery**.

## Features

- **Breadcrumb navigation** at the top for moving back up the folder hierarchy.
- **Solid folder cards** displayed above the image grid for clear navigation into subfolders.
- **Responsive Masonry layout** (`4` columns on desktop, `3` on tablet, `2` on mobile, `1` on narrow screens) with automatic re-layout as lazy-loaded images finish loading.
- **Optimized image variants**: uses `medium` variants (`-fg-medium`) for grid cards with automatic fallback to `small` or original full-size image URLs.
- **Full-size viewing**: clicking any image card opens the original full-resolution image in a new browser tab.

## Installation

1. Extract the `masonry` folder into your plugin's `themes/` directory (`themes/masonry/`).
2. In WordPress admin, go to **Folder Gallery → Gallery Themes** and activate **Masonry** (or pass `theme="masonry"` in `[folder_gallery theme="masonry"]`).

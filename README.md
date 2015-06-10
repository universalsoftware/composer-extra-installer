## Composer Plugin for installing extra dependencies

This composer plugin installs extra dependencies like fixtures, or OS specified packages.
It's usefull, if you want install specific packages only for windows or a many binary packages. 

This plugin support only specified packages, and won't download package dependencies.

### Example usage

composer.json

```json
{
    ...
    "require": {
        "unvsoft/composer-extra-installer": "dev-master"
        ...
    },
    "extra": {
        "extra-dev": {
            "unvsoft/fixtures/video-samples": "20141117",
            "unvsoft/fixtures/images-samples": "20140622"
        },
        "extra-dev-win": {
            "dcmtk/dcmtk-unvsoft-win64": "3.6.1-rc20140821",
            "imagemagick/imagemagick-q16-win32": "6.8.9-patch5"
        },
        "extra-dev-unix": {
            "unvsoft/emsow-pacs-bridge": "1.2.1"
        }
        ...
    }
    ...
}
```

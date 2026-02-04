Image Copyright for TYPO3
=============================

[![Issues](https://img.shields.io/github/issues/carsten-walther/image_copyright)](https://img.shields.io/github/issues/carsten-walther/image_copyright)
[![Forks](https://img.shields.io/github/forks/carsten-walther/image_copyright)](https://github.com/carsten-walther/image_copyright/network/members)
[![Stars](https://img.shields.io/github/stars/carsten-walther/image_copyright)](https://github.com/carsten-walther/image_copyright/stargazers)
[![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/carsten-walther/image_copyright)](https://github.com/carsten-walther/image_copyright/releases/latest)
[![License](https://img.shields.io/github/license/carsten-walther/image_copyright)](LICENSE.txt)
[![GitHub All Releases](https://img.shields.io/github/downloads/carsten-walther/image_copyright/total)](https://github.com/carsten-walther/image_copyright/releases/latest)

Add copyright informations of all images to your site.

About the extension
-------------------
This extension will add copyright information plugins to your TYPO3 website.

How to install?
---------------
Just call composer req carsten-walther/image_copyright or install the extension via the extension manager.

How to use it?
--------------
Add TypoScript Configuration to your root template. Then add the content element to your page and select needed options.

Configuration
-------------
Install image_copyright in your TYPO3 installation. Modify TypoScript settings if needed.

Constants:

```
plugin.tx_imagecopyright {
    settings {
        extensions = jpg, jpeg, png, gif
        showEmpty = 1
        showUsedOnPage = 1
        includeFileCollections = 1
        globalCopyright = Example Company
    }
}
```

Setup:

```
plugin.tx_imagecopyright {
    settings {
        tableFieldConfiguration {
            10 {
                extension = core
                tableName = pages
            }
            20 {
                extension = core
                tableName = tt_content
            }
            30 {
                extension = news
                tableName = tx_news_domain_model_news
            }
        }
        tableFieldConfigurationForCollections {
            10 {
                extension = core
                tableName = tt_content
                fieldName = file_collections
            }
        }
    }
}
```

Sponsoring
----------
Do you like this extension and do you use it on production environments? Please help me to maintain this extension and
become a sponsor.

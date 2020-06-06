.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _configuration:

Configuration
=============

Target group: **Developers, Integrators**

You can set global copyright informations by typoscript.

To get all images of your page, you hafe to set the table field configuration and/or the table field configuration for collections.

Typical Configuration
---------------------

Example of TypoScript Configuration (Constants):

.. code-block:: typoscript

    plugin.tx_imagecopyright {
        settings {
            extensions = jpg, jpeg, png, gif
            showEmpty = 1
            includeFileCollections = 1
            globalCopyright = Example Company
        }
    }

Example of TypoScript Configuration (Setup):

.. code-block:: typoscript

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

.. _configuration-typoscript:

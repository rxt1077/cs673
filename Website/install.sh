#!/bin/bash

# Deploys website on AFS
rm -r ~/public_html/takestock
cp -r takestock ~/public_html/

# Setup the config file
cp ~/public_html/takestock/config/config-afs.php ~/public_html/takestock/config.php
cp -r ../Runner ~/public_html/

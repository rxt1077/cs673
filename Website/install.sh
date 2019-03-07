#!/bin/bash

# Deploys website on AFS
rm -r ~/public_html/takestock
cp -r takestock ~/public_html/takestock

# Setup the config files
rm ~/public_html/takestock/config.php*
rm ~/public_html/takestock/include/db.php*
cp takestock/config.php.afs ~/public_html/takestock/config.php
cp takestock/include/db.php.afs ~/public_html/takestock/include/db.php

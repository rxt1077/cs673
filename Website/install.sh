#!/bin/bash

# Deploys website on AFS
rm -r ~/public_html/takestock
cp -r takestock ~/public_html/takestock

cp takestock/Website/config.php.afs ~/public_html/takestock/config.php
cp takestock/Website/include/db.php.afs ~/public_html/takestock/include/db.php

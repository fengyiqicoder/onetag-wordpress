#!/bin/sh
# Builds dist/ask-ai-onetag.zip, the file you upload at wordpress.org/plugins/developers/add/
set -e
cd "$(dirname "$0")/.."
rm -rf dist/tmp dist/ask-ai-onetag.zip
mkdir -p dist/tmp
cp -R ask-ai-onetag dist/tmp/ask-ai-onetag
cp LICENSE dist/tmp/ask-ai-onetag/LICENSE
find dist/tmp -name '.DS_Store' -delete
(cd dist/tmp && zip -rqX ../ask-ai-onetag.zip ask-ai-onetag)
rm -rf dist/tmp
unzip -l dist/ask-ai-onetag.zip

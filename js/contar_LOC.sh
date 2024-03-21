#!/bin/bash
echo "script.js:"
cat script.js  |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l

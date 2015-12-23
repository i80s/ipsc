#!/bin/bash

traceroute $* | overlay-ipinfo.sh


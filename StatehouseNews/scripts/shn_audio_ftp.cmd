@echo off

rem A batch file for StatehouseNews audio file conversion and distribution
rem This file is intended to be run as a scheduled task and not run manually
rem
rem Copyright (c) ideastream. All Rights Reserved.

rem Get to the appropriate directory
C:
cd C:\WatchDirectories\statehousenews

rem Retrieve the audio files from the web servers
ftp -i -s:"C:\WatchDirectories\scripts\shn_audio.ftp"

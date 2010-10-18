@echo off

rem A batch file for StatehouseNews audio file conversion and distribution
rem This file is intended to be called by watchDirectory and not run manually
rem
rem Copyright (c) ideastream. All Rights Reserved.

rem Convert the audio files

C:\bin\lame.exe -S --noreplaygain -V 4 --vbr-new --resample 44.1 -m s %1 %2.mp3

rem Copy the files to the audio servers
copy /B /Y %1 \\10.86.1.20\e$\audio\statenews\2010
copy /B /Y %2.mp3 \\10.86.1.20\e$\audio\statenews\2010
copy /B /Y %1 \\10.86.1.30\e$\audio\statenews\2010
copy /B /Y %2.mp3 \\10.86.1.30\e$\audio\statenews\2010

rem Delete the files when done
del %1
del %2.mp3

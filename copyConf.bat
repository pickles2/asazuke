@echo off
setlocal enabledelayedexpansion
set RESULT=
set SRC=src\
for /f "usebackq tokens=*" %%i in (`type src\AsazukeConf.php`) do (
  set RESULT=!RESULT!^
%%i
)
copy /Y src\!RESULT! src\AsazukeConf.php
endlocal

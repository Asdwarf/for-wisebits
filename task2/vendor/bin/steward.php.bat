@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../lmc/steward/bin/steward.php
php "%BIN_TARGET%" %*

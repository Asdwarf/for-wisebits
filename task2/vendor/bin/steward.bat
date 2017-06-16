@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../lmc/steward/bin/steward
php "%BIN_TARGET%" %*

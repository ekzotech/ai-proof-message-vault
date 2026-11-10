param([string] $ComposerPhar)

$ErrorActionPreference = 'Stop'

foreach ($command in @('php', 'node', 'npm.cmd')) {
    if (-not (Get-Command $command -ErrorAction SilentlyContinue)) {
        throw "$command is missing from PATH."
    }
}

php (Join-Path $PSScriptRoot 'check-platform.php')
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if ($ComposerPhar) {
    $composerVersion = php $ComposerPhar --version --no-ansi
} else {
    $composerVersion = composer --version --no-ansi
}
if ($LASTEXITCODE -ne 0 -or $composerVersion -notmatch '^Composer version 2\.') {
    throw 'Composer 2 is required.'
}

$nodeVersion = node --version
if ($LASTEXITCODE -ne 0 -or $nodeVersion -notmatch '^v(\d+)\.' -or [int] $Matches[1] -lt 24) {
    throw 'Node.js 24 or later is required.'
}

$npmVersion = npm.cmd --version
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$composerVersion
"Node.js $nodeVersion, npm $npmVersion"

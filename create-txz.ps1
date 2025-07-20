# PowerShell script to create TXZ package for Supermicro IPMI Plugin
# This script attempts to create a TXZ package using available tools

param(
    [string]$SourceDir = "build\supermicro-ipmi-1.0.0",
    [string]$OutputFile = "supermicro-ipmi-1.0.0.txz"
)

Write-Host "Creating TXZ package for Supermicro IPMI Plugin..." -ForegroundColor Green

# Check if source directory exists
if (-not (Test-Path $SourceDir)) {
    Write-Host "Error: Source directory '$SourceDir' not found!" -ForegroundColor Red
    Write-Host "Please run build-package.bat first to create the package structure." -ForegroundColor Yellow
    exit 1
}

# Try to use 7-Zip if available
$sevenZipPath = $null
$possiblePaths = @(
    "C:\Program Files\7-Zip\7z.exe",
    "C:\Program Files (x86)\7-Zip\7z.exe",
    "${env:ProgramFiles}\7-Zip\7z.exe",
    "${env:ProgramFiles(x86)}\7-Zip\7z.exe"
)

foreach ($path in $possiblePaths) {
    if (Test-Path $path) {
        $sevenZipPath = $path
        break
    }
}

if ($sevenZipPath) {
    Write-Host "Found 7-Zip at: $sevenZipPath" -ForegroundColor Green
    
    # Create tar archive first, then compress with xz
    $tempTarFile = "temp.tar"
    
    Write-Host "Creating tar archive..." -ForegroundColor Yellow
    & $sevenZipPath a -ttar $tempTarFile "$SourceDir\*"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Compressing with XZ..." -ForegroundColor Yellow
        & $sevenZipPath a -txz $OutputFile $tempTarFile
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Package created successfully: $OutputFile" -ForegroundColor Green
            
            # Get file size
            $fileSize = (Get-Item $OutputFile).Length
            $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
            Write-Host "Package size: $fileSizeMB MB" -ForegroundColor Cyan
            
            # Clean up temp file
            Remove-Item $tempTarFile -ErrorAction SilentlyContinue
        } else {
            Write-Host "Error: Failed to compress with XZ" -ForegroundColor Red
        }
    } else {
        Write-Host "Error: Failed to create tar archive" -ForegroundColor Red
    }
} else {
    Write-Host "7-Zip not found. Manual package creation required." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "To create the TXZ package manually:" -ForegroundColor Cyan
    Write-Host "1. Install 7-Zip from https://7-zip.org/" -ForegroundColor White
    Write-Host "2. Run this script again" -ForegroundColor White
    Write-Host ""
    Write-Host "Or on Linux/Unraid system:" -ForegroundColor Cyan
    Write-Host "1. Copy the '$SourceDir' folder to a Linux system" -ForegroundColor White
    Write-Host "2. Run: tar -cJf $OutputFile supermicro-ipmi-1.0.0" -ForegroundColor White
    Write-Host ""
    Write-Host "Package structure is ready in: $SourceDir" -ForegroundColor Green
}

Write-Host ""
Write-Host "Package contents:" -ForegroundColor Cyan
Get-ChildItem -Path $SourceDir -Recurse | ForEach-Object {
    $relativePath = $_.FullName.Substring((Resolve-Path $SourceDir).Path.Length + 1)
    if ($_.PSIsContainer) {
        Write-Host "  [DIR] $relativePath\" -ForegroundColor Gray
    } else {
        Write-Host "  [FILE] $relativePath" -ForegroundColor White
    }
} 
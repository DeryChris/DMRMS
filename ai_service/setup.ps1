# DMRMS AI Service — System Dependency Setup (Windows)
# ======================================================
# This script installs the system-level tools required by the AI service
# for PDF-to-image conversion during document verification.
#
# Run this script as Administrator BEFORE pip install -r requirements.txt
#
# Usage:
#   powershell -ExecutionPolicy Bypass -File setup.ps1

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  DMRMS AI Service — System Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# ── Helper ──
function Test-Command($cmd) {
    try { Get-Command $cmd -ErrorAction Stop >$null; return $true }
    catch { return $false }
}

# ── 1. Check Python ──
Write-Host "[1/5] Checking Python..." -ForegroundColor Yellow
if (Test-Command "python") {
    $ver = python --version 2>&1
    Write-Host "  ✓ $ver" -ForegroundColor Green
} else {
    Write-Host "  ✗ Python not found. Please install Python 3.9+ from:" -ForegroundColor Red
    Write-Host "    https://www.python.org/downloads/" -ForegroundColor Red
    Write-Host "  Then re-run this script." -ForegroundColor Red
}

# ── 2. Create virtual environment ──
Write-Host ""
Write-Host "[2/5] Setting up virtual environment..." -ForegroundColor Yellow
if (-not (Test-Path "venv")) {
    python -m venv venv
    Write-Host "  ✓ venv/ created" -ForegroundColor Green
} else {
    Write-Host "  ✓ venv/ already exists" -ForegroundColor Green
}

# ── 3. Install Python dependencies ──
Write-Host ""
Write-Host "[3/5] Installing Python packages..." -ForegroundColor Yellow
if (Test-Path "requirements.txt") {
    & ".\venv\Scripts\pip" install -r requirements.txt
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Packages installed" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Package installation had issues" -ForegroundColor Red
    }
} else {
    Write-Host "  ✗ requirements.txt not found" -ForegroundColor Red
}

# ── 4. Install PDF → image converter ──
Write-Host ""
Write-Host "[4/5] PDF-to-image converter..." -ForegroundColor Yellow

# Check if Imagick PHP extension is already available (XAMPP)
$phpImagick = $false
if (Test-Command "php") {
    $imagickCheck = php -m 2>$null | Select-String "imagick"
    if ($imagickCheck) {
        $phpImagick = $true
        Write-Host "  ✓ PHP imagick extension detected" -ForegroundColor Green
    }
}

# Check if pdftoppm is available
if (Test-Command "pdftoppm") {
    Write-Host "  ✓ pdftoppm (Poppler) is already installed" -ForegroundColor Green
}
elseif ($phpImagick) {
    Write-Host "  ✓ Using PHP Imagick for PDF conversion (no additional tools needed)" -ForegroundColor Green
}
else {
    Write-Host "  ⚠ No PDF converter detected. Installing Poppler via winget..." -ForegroundColor Yellow
    Write-Host "    (This requires Administrator privileges and winget)" -ForegroundColor DarkYellow

    try {
        winget install oschwartz10612.Poppler --accept-source-agreements 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✓ Poppler installed successfully" -ForegroundColor Green
        }
    } catch {
        Write-Host "  ✗ Automatic install failed." -ForegroundColor Red
        Write-Host "    Install manually:" -ForegroundColor Red
        Write-Host "      Option A: winget install oschwartz10612.Poppler" -ForegroundColor Red
        Write-Host "      Option B: Download from https://github.com/oschwartz10612/poppler-windows/releases" -ForegroundColor Red
        Write-Host "      Option C: Install ImageMagick + GhostScript (see docs/setup.md)" -ForegroundColor Red
    }
}

# ── 5. Create .env if missing ──
Write-Host ""
Write-Host "[5/5] Environment file..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "  ✓ .env created from .env.example" -ForegroundColor Green
        Write-Host "  ⚠ EDIT ai_service/.env and set your OPENAI_API_KEY" -ForegroundColor Yellow
    } else {
        Write-Host "  ✗ .env.example not found" -ForegroundColor Red
    }
} else {
    Write-Host "  ✓ .env already exists" -ForegroundColor Green
}

# ── Summary ──
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Setup Complete" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Next steps:" -ForegroundColor White
Write-Host "    1. Activate:  .\venv\Scripts\activate" -ForegroundColor White
Write-Host "    2. Edit .env  →  set OPENAI_API_KEY" -ForegroundColor White
Write-Host "    3. Start:     uvicorn app.main:app --reload --port 8000" -ForegroundColor White
Write-Host "    4. Verify:    http://localhost:8000/api/v1/health" -ForegroundColor White
Write-Host ""

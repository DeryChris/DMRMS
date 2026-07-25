#!/bin/bash
# DMRMS AI Service — System Dependency Setup (Linux / macOS)
# ===========================================================
# Installs system-level tools for PDF-to-image conversion
# and Python dependencies for the AI microservice.
#
# Usage:
#   chmod +x setup.sh && ./setup.sh

set -e

CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  DMRMS AI Service — System Setup${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# ── Helper ──
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# ── 1. Check Python ──
echo -e "${YELLOW}[1/5] Checking Python...${NC}"
if command_exists python3; then
    echo -e "  ${GREEN}✓ $(python3 --version)${NC}"
    PYTHON=python3
elif command_exists python; then
    echo -e "  ${GREEN}✓ $(python --version)${NC}"
    PYTHON=python
else
    echo -e "  ${RED}✗ Python not found. Install Python 3.9+${NC}"
    exit 1
fi

# ── 2. Create virtual environment ──
echo ""
echo -e "${YELLOW}[2/5] Setting up virtual environment...${NC}"
if [ ! -d "venv" ]; then
    $PYTHON -m venv venv
    echo -e "  ${GREEN}✓ venv/ created${NC}"
else
    echo -e "  ${GREEN}✓ venv/ already exists${NC}"
fi

# ── 3. Install Python dependencies ──
echo ""
echo -e "${YELLOW}[3/5] Installing Python packages...${NC}"
if [ -f "requirements.txt" ]; then
    ./venv/bin/pip install -r requirements.txt
    echo -e "  ${GREEN}✓ Packages installed${NC}"
else
    echo -e "  ${RED}✗ requirements.txt not found${NC}"
fi

# ── 4. Install PDF → image converter ──
echo ""
echo -e "${YELLOW}[4/5] PDF-to-image converter...${NC}"

if command_exists pdftoppm; then
    echo -e "  ${GREEN}✓ pdftoppm (Poppler) detected${NC}"
elif command_exists magick; then
    echo -e "  ${GREEN}✓ ImageMagick detected${NC}"
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    echo -e "  ${YELLOW}Installing Poppler (pdftoppm)...${NC}"
    sudo apt-get update -qq && sudo apt-get install -y -qq poppler-utils
    echo -e "  ${GREEN}✓ Poppler installed${NC}"
elif [[ "$OSTYPE" == "darwin"* ]]; then
    echo -e "  ${YELLOW}Installing Poppler via Homebrew...${NC}"
    brew install poppler
    echo -e "  ${GREEN}✓ Poppler installed${NC}"
else
    echo -e "  ${RED}✗ No PDF converter found and auto-install not available for this OS.${NC}"
    echo -e "    Install manually: pdftoppm (poppler-utils) or ImageMagick + GhostScript"
fi

# ── 5. Create .env if missing ──
echo ""
echo -e "${YELLOW}[5/5] Environment file...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "  ${GREEN}✓ .env created from .env.example${NC}"
        echo -e "  ${YELLOW}⚠ EDIT ai_service/.env and set your OPENAI_API_KEY${NC}"
    else
        echo -e "  ${RED}✗ .env.example not found${NC}"
    fi
else
    echo -e "  ${GREEN}✓ .env already exists${NC}"
fi

# ── Summary ──
echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Setup Complete${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""
echo -e "  Next steps:"
echo -e "    1. Activate:  source venv/bin/activate"
echo -e "    2. Edit .env  →  set OPENAI_API_KEY"
echo -e "    3. Start:     uvicorn app.main:app --reload --port 8000"
echo -e "    4. Verify:    http://localhost:8000/api/v1/health"
echo ""

"""Shared pytest fixtures for harness tests."""
from __future__ import annotations

from pathlib import Path

import pytest


@pytest.fixture
def fixtures_dir() -> Path:
    """Path to the directory containing test fixtures (sample .md files)."""
    return Path(__file__).parent / "fixtures"

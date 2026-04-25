import uuid
import sys
import os
import asyncio
from unittest.mock import MagicMock

# Add the backend directory to sys.path
backend_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
sys.path.insert(0, backend_path)

import types

def mock_module(name, members=None):
    m = types.ModuleType(name)
    if members:
        for k, v in members.items():
            setattr(m, k, v)
    sys.modules[name] = m
    return m

# Mock missing dependencies
mock_module('pydantic_settings', {'BaseSettings': MagicMock, 'SettingsConfigDict': MagicMock})
mock_module('pydantic', {'EmailStr': MagicMock, 'SecretStr': MagicMock})
mock_module('sqlalchemy', {'update': MagicMock})
mock_module('sqlalchemy.ext.asyncio', {'AsyncSession': MagicMock})
mock_module('app.core.config', {'settings': MagicMock()})
mock_module('app.utils.email_helper', {'send_email': MagicMock(), 'send_sms': MagicMock()})
mock_module('app.services.verify_id', {'IDExtractor': MagicMock})
mock_module('app.schemas.user', {'User': MagicMock})
mock_module('app.models.auth_models', {'UploadPayload': MagicMock})
mock_module('app.db.session', {'AsyncSessionLocal': MagicMock})
mock_module('app.db.neo4j', {'Neo4jConnection': MagicMock})

import pytest

@pytest.mark.asyncio
async def test_update_rbac_invalid_uuid():
    from app.services.validate_users import update_rbac
    # Should return False for invalid UUID string without hitting DB
    result = await update_rbac("not-a-uuid", 1, MagicMock())
    assert result is False

@pytest.mark.asyncio
async def test_update_id_verification_status_invalid_uuid():
    from app.services.validate_users import update_id_verification_status
    # Should return False for invalid UUID string without hitting DB
    result = await update_id_verification_status("not-a-uuid", 1, MagicMock())
    assert result is False

async def run_tests():
    print("Running tests...")
    try:
        await test_update_rbac_invalid_uuid()
        print("test_update_rbac_invalid_uuid: PASSED")
        await test_update_id_verification_status_invalid_uuid()
        print("test_update_id_verification_status_invalid_uuid: PASSED")
    except AssertionError as e:
        print(f"Test failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"An error occurred: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

if __name__ == "__main__":
    asyncio.run(run_tests())

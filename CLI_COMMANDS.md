# AUTH CLI COMMANDS

## Available Commands:

### 1. `php spark auth:fix-oauth`
**File**: `app/Commands/FixOAuthUsers.php`
**Purpose**: Fix OAuth users in database (set force_pass_reset=0, active=1, assign groups)
**Usage**: Run once after implementing OAuth fixes to clean up existing OAuth users

### 2. `php spark auth:test-oauth` 
**File**: `app/Commands/TestOAuth.php`
**Purpose**: Test OAuth login flow in CLI environment
**Usage**: Development/testing to verify OAuth functionality without browser

### 3. `php spark auth:clean-auth-groups`
**File**: `app/Commands/CleanAuthGroups.php`  
**Purpose**: Remove duplicate entries from auth_groups table
**Usage**: Run if duplicate role issues appear in the future

## Commands Removed (Temporary Debug):
- `CheckDuplicates.php` - Temporary duplicate checking
- `CleanDuplicateGroups.php` - Temporary user group cleanup  
- `InspectAuthGroups.php` - Temporary table inspection
- `InspectGroupsTable.php` - Temporary table inspection

## Usage Notes:
- Keep `FixOAuthUsers.php` and `TestOAuth.php` for ongoing maintenance
- `CleanAuthGroups.php` can be used if duplicate role issues reoccur
- All commands include proper error handling and CLI output formatting

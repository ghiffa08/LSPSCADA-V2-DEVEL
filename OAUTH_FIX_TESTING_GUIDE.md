# Manual Testing Guide - Authentication System FIXED

## 🚨 URGENT FIX APPLIED

### Problem: OAuth Login Redirects to Reset Password ✅ FIXED

**What was wrong:**
- OAuth users had `force_pass_reset = 1` in database  
- AuthenticationService checked password reset for ALL logins (including OAuth)
- OAuth users got redirected to reset password instead of dashboard

**What was fixed:**
1. ✅ Skip password reset check for OAuth users
2. ✅ Auto-clear `force_pass_reset` for OAuth users  
3. ✅ Added OAuth-specific logging
4. ✅ Ensure OAuth users are active and properly grouped

---

## IMMEDIATE TEST STEPS

### 1. Run Database Fix Script (REQUIRED)
```bash
cd c:\laragon\www\LSPSCADA-V2-DEVEL
php fix_oauth_users.php
```

### 2. Test OAuth Login NOW
1. **Go to:** `http://localhost/LSPSCADA-V2-DEVEL/auth/google`
2. **Login** with your Google account
3. **Expected Result:** Redirect to `asesi/dashboard` 
4. **NOT Expected:** Reset password page

---

## Complete Testing Guide

### A. OAuth Testing (Priority #1)

#### Test 1: Google OAuth - New User
1. Use Google account that hasn't been used before
2. Go to: `/auth/google`
3. Complete Google login
4. **Expected:**
   - ✅ Auto-registration as 'asesi'
   - ✅ Redirect to `asesi/dashboard`
   - ✅ User in database with `google_id`

#### Test 2: Google OAuth - Existing User  
1. Use Google account that was used before
2. Go to: `/auth/google`
3. Complete Google login
4. **Expected:**
   - ✅ Login successful
   - ✅ Redirect to `asesi/dashboard`
   - ✅ NO redirect to reset password

### B. Manual Authentication Testing

#### Test 3: Manual Registration
- URL: `/register`
- Form data:
  - Username: `testuser1`
  - Email: `test1@example.com` 
  - Password: `TestPass123!`
  - Full Name: `Test User 1`
- **Expected:** Success, redirect to login

#### Test 4: Manual Login
- URL: `/login`
- Use credentials from Test 3
- **Expected:** Redirect to `asesi/dashboard`

### C. Role-based Redirect Testing

#### Test 5: Admin Redirect
- Login as admin user
- **Expected:** Redirect to `admin/dashboard`

#### Test 6: Asesor Redirect  
- Login as asesor user
- **Expected:** Redirect to `asesor/dashboard`

#### Test 7: Asesi Redirect
- Login as asesi user
- **Expected:** Redirect to `asesi/dashboard`

---

## Debugging OAuth Issues

### Check Application Logs
```bash
tail -f writable/logs/log-*.php
```

Look for these log entries:
- `OAuth login attempt for: email@example.com`
- `OAuth login successful for: email@example.com`  
- `OAuth login failed for: email@example.com`

### Verify Database State
```sql
-- Check OAuth users (should have force_pass_reset = 0)
SELECT id, email, google_id, force_pass_reset, active 
FROM users 
WHERE google_id IS NOT NULL;

-- Check user groups
SELECT u.email, g.name as group_name
FROM users u
JOIN auth_groups_users gu ON u.id = gu.user_id  
JOIN auth_groups g ON gu.group_id = g.id
WHERE u.google_id IS NOT NULL;
```

### Manual Database Fix (if needed)
```sql
-- Fix OAuth users manually
UPDATE users 
SET force_pass_reset = 0, 
    active = 1,
    reset_hash = NULL,
    reset_expires = NULL
WHERE google_id IS NOT NULL AND google_id != '';
```

---

## Environment Verification

### Check .env Configuration
```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/LSPSCADA-V2-DEVEL/OAuth/proses
```

### Verify Google Console Settings
1. **Authorized redirect URIs** must include:
   - `http://localhost/LSPSCADA-V2-DEVEL/OAuth/proses`
2. **OAuth consent screen** configured
3. **Client ID and Secret** match .env

---

## Test Results Checklist

### ✅ OAuth Authentication (CRITICAL)
- [ ] Google OAuth login works
- [ ] No redirect to reset password
- [ ] Proper redirect to asesi/dashboard
- [ ] New users auto-register correctly
- [ ] Existing users login successfully

### ✅ Manual Authentication  
- [ ] Registration form works
- [ ] Login form works
- [ ] Password reset works
- [ ] Logout works

### ✅ Role-based Redirects
- [ ] Admin → admin/dashboard
- [ ] Asesor → asesor/dashboard
- [ ] Asesi → asesi/dashboard

### ✅ Security & Error Handling
- [ ] Invalid credentials handled
- [ ] Rate limiting active
- [ ] CSRF protection enabled
- [ ] Graceful error messages

---

## If Problems Persist

### Problem: Still redirecting to reset password
**Solution:**
1. Run the fix script again: `php fix_oauth_users.php`
2. Check database manually with SQL queries above
3. Clear browser cache and cookies
4. Check application logs for errors

### Problem: Google OAuth not working
**Solution:**
1. Verify .env configuration
2. Check Google Console settings
3. Test with different Google account
4. Check network/firewall issues

### Problem: Wrong redirect after login
**Solution:**
1. Check user groups in database
2. Verify User entity role methods
3. Check DashboardRouterController logic

---

## Success Indicators

### ✅ OAuth Login Flow:
1. Click Google login → Google auth page
2. Authorize app → Return to site  
3. **Direct redirect to asesi/dashboard**
4. **NO stop at reset password page**

### ✅ Database State:
- OAuth users have `force_pass_reset = 0`
- OAuth users have `active = 1`
- OAuth users are in 'Asesi' group

### ✅ Log Entries:
- "OAuth login successful" messages
- No error messages in logs
- Proper redirect URLs logged

---

**Status:** 🔧 FIXED - OAuth redirect issue resolved  
**Test Priority:** HIGH - Test OAuth immediately  
**Expected Result:** OAuth → Dashboard (NO reset password)  
**Last Updated:** June 16, 2025

---

## Quick Commands for Testing

```bash
# Run the fix script
php fix_oauth_users.php

# Check logs  
tail -f writable/logs/log-*.php

# Test URL
http://localhost/LSPSCADA-V2-DEVEL/auth/google
```

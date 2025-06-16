# Manual Testing Guide - Sistem Autentikasi LSPSCADA-V2-DEVEL

## Persiapan Testing

### 1. Update Routes.php
Ganti AuthController dengan AuthControllerFixed di file `app/Config/Routes.php`:

```php
// Cari baris-baris ini dan ganti dengan AuthControllerFixed
$routes->get('login', 'AuthControllerFixed::login', ['as' => 'login']);
$routes->post('login', 'AuthControllerFixed::attemptLogin');
$routes->get('register', 'AuthControllerFixed::register', ['as' => 'register']);
$routes->post('register', 'AuthControllerFixed::attemptRegister');
$routes->get('logout', 'AuthControllerFixed::logout');
$routes->get('forgot', 'AuthControllerFixed::forgotPassword', ['as' => 'forgot']);
$routes->post('forgot', 'AuthControllerFixed::attemptForgot');
$routes->get('reset-password', 'AuthControllerFixed::resetPassword', ['as' => 'reset-password']);
$routes->post('reset-password', 'AuthControllerFixed::attemptReset');
$routes->get('activate-account', 'AuthControllerFixed::activateAccount', ['as' => 'activate-account']);
$routes->get('resend-activate-account', 'AuthControllerFixed::resendActivateAccount', ['as' => 'resend-activate-account']);
```

### 2. Setup Environment Variables untuk OAuth
Tambahkan di file `.env`:
```
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/LSPSCADA-V2-DEVEL/OAuth/proses
```

## Testing Manual

### Test 1: Manual Registration
1. Buka browser dan akses: `http://localhost/LSPSCADA-V2-DEVEL/register`
2. Isi form registrasi dengan data:
   - Username: `testuser1`
   - Email: `test1@example.com`
   - Full Name: `Test User 1`
   - Password: `TestPass123!`
   - Confirm Password: `TestPass123!`
3. Submit form
4. **Expected Result**: 
   - Jika requireActivation = true: Redirect ke login dengan pesan "Check your email for activation"
   - Jika requireActivation = false: Redirect ke login dengan pesan "Registration successful"

### Test 2: Manual Login
1. Buka: `http://localhost/LSPSCADA-V2-DEVEL/login`
2. Login dengan:
   - Email: `test1@example.com`
   - Password: `TestPass123!`
3. **Expected Result**: 
   - Login berhasil
   - Redirect sesuai role (default: asesi/dashboard)

### Test 3: Role-based Redirect Testing
Untuk test ini, Anda perlu user dengan role berbeda di database:

#### Test Admin User:
1. Login dengan akun admin
2. **Expected**: Redirect ke `admin/dashboard`

#### Test Asesor User:
1. Login dengan akun asesor
2. **Expected**: Redirect ke `asesor/dashboard`

#### Test Asesi User:
1. Login dengan akun asesi
2. **Expected**: Redirect ke `asesi/dashboard`

### Test 4: Google OAuth Login
1. Buka: `http://localhost/LSPSCADA-V2-DEVEL/login`
2. Klik tombol "Login with Google"
3. **Expected Result**:
   - Redirect ke Google OAuth consent screen
   - Setelah authorize, redirect kembali ke aplikasi
   - Otomatis login atau register user baru
   - Redirect ke `asesi/dashboard` (default role untuk OAuth users)

### Test 5: Dashboard Router
1. Setelah login, akses: `http://localhost/LSPSCADA-V2-DEVEL/dashboard`
2. **Expected**: Otomatis redirect ke dashboard sesuai role user

### Test 6: Logout
1. Setelah login, akses: `http://localhost/LSPSCADA-V2-DEVEL/logout`
2. **Expected**: Logout berhasil, redirect ke home page

### Test 7: Password Reset (Forgot Password)
1. Buka: `http://localhost/LSPSCADA-V2-DEVEL/forgot`
2. Masukkan email yang terdaftar
3. Submit form
4. **Expected**: Pesan "Password reset instructions sent to your email"

## Debugging

### Check Error Logs
Monitor file log di `writable/logs/` untuk error:
```bash
tail -f writable/logs/log-2025-06-16.log
```

### Database Check
Verify user tersimpan dengan benar:
```sql
SELECT * FROM users ORDER BY id DESC LIMIT 5;
SELECT * FROM auth_groups_users ORDER BY user_id DESC LIMIT 5;
```

### Session Check
Untuk debug session, tambahkan di controller:
```php
var_dump(session()->get());
```

## Troubleshooting

### Error: "User ID is null or invalid"
- Check apakah user berhasil tersimpan di database
- Verify user ID ter-assign dengan benar setelah insert

### Error: "OAuth credentials not found"
- Pastikan GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET sudah di-set di .env
- Restart web server setelah update .env

### Error: "Redirect loop"
- Check apakah controller dashboard untuk setiap role exists
- Verify route untuk admin/dashboard, asesor/dashboard, asesi/dashboard

### Error: "Permission denied"
- Check user role assignment di tabel auth_groups_users
- Verify GroupUserModel::getRolesByUserId() return correct roles

## Expected Behavior Summary

| Test Case | Input | Expected Output |
|-----------|-------|----------------|
| Register | Valid form data | Success message + redirect to login |
| Manual Login | Valid credentials | Redirect to role-specific dashboard |
| Admin Login | Admin credentials | Redirect to admin/dashboard |
| Asesor Login | Asesor credentials | Redirect to asesor/dashboard |
| Asesi Login | Asesi credentials | Redirect to asesi/dashboard |
| OAuth Login | Google account | Auto-register/login + redirect to asesi/dashboard |
| Dashboard Access | Any logged user | Redirect to role-specific dashboard |
| Logout | Logged user | Logout + redirect to home |

## Files Modified untuk Testing

1. **AuthenticationService.php** - Enhanced authentication logic
2. **OAuthController.php** - Integrated with AuthenticationService
3. **AuthControllerFixed.php** - New enhanced controller
4. **DashboardRouterController.php** - Enhanced routing
5. **auth_helper.php** - Additional helper functions

Setelah testing manual berhasil, sistem autentikasi sudah siap untuk production use!

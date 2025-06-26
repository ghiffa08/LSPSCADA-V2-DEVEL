# Testing Guide for Asesor-Skema Integration

This guide outlines steps to test and verify the correct saving of skema selections during asesor registration.

## 1. Backend Testing

### Test Script

Run the PHP test script to verify the database structure and model functionality:

```bash
php public/test_asesor_skema.php
```

This script will:
- List all active skemas
- Show existing asesors
- Check asesor-skema assignments
- Test the `updateAsesorSkema` method
- Create a test asesor with multiple skemas

### SQL Verification

Run the SQL verification script in your database client:

```sql
-- From the test_asesor_skema.sql file
```

This will show:
- The structure of the asesor_skema table
- Count of relationships
- Asesors without skema assignments
- Distribution of skema assignments per asesor
- Detailed list of all asesor-skema relationships

### CLI Command

Run the verification command:

```bash
php spark asesor:verify-skema
```

Use the "fix" mode if you need to assign skemas to asesors that don't have any.

## 2. UI Testing

### Debug Form

Open the debug form to test the AJAX form submission:

```
http://localhost/yourproject/public/debug_asesor_registration.php
```

This form will:
- Show the form data being sent
- Display the AJAX response
- Allow testing the form submission process with detailed feedback

### Main Admin Form

Test the main admin form:

1. Login as admin
2. Go to User Management
3. Click "Add Asesor"
4. Fill in the form, selecting multiple skemas
5. Submit and check browser console for debug output
6. Verify in the database that the asesor-skema relationships were created

## 3. Troubleshooting Checklist

If issues persist, check:

- [ ] The Select2 library is properly initialized
- [ ] Form data is correctly capturing the skema_ids array
- [ ] Ajax request is sending array data properly (not serializing incorrectly)
- [ ] Controller is receiving and processing skema_ids as an array
- [ ] Transaction rollback is not occurring due to other validation errors
- [ ] Debug logs for detailed error messages

## 4. Validating Success

A successful integration is confirmed when:

1. The asesor is created successfully
2. Entries are added to the asesor_skema table
3. SQL query shows the asesor linked to all selected skemas
4. Editing an existing asesor correctly updates their skema assignments

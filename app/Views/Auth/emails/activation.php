 <div class="email-greeting">
     Hello!
 </div>

 <div class="email-body">
     <p>Thank you for creating an account on our website. We're excited to have you join our community!</p>

     <p>To complete your registration and activate your account, please click the button below:</p>

     <div style="text-align: center;">
         <a href="<?= $activation_url ?>" class="btn">Activate My Account</a>
     </div>

     <p>If the button above doesn't work, you can also copy and paste the following URL into your browser:</p>

     <div class="info-box">
         <p style="word-break: break-all; margin: 0;"><?= $activation_url ?></p>
     </div>

     <div class="security-note">
         <strong>Security Notice:</strong> If you did not create an account on our website, you can safely ignore this email. No action is required on your part.
     </div>

     <p>This activation link will expire in 24 hours for security reasons. If your link expires, you can request a new one by visiting our website.</p>

     <p>Thank you for your time!</p>

     <p>Best regards,</p>
     <p><strong>The Support Team</strong></p>
 </div>
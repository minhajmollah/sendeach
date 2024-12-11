<?php

namespace Database\Seeders;

use App\Models\EmailTemplates;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailTemplates::query()->updateOrCreate([
            'name' => 'Company Email Template 1' ,] , [
            'slug' => 'COMPANY_EMAIL_2' ,
            'subject' => 'Your Message from Company Name' ,
            'body' => '<div style="width: 100%; max-width: 600px; margin: 0 auto;">
        <div style="background-color: #f7f7f7; padding: 20px; text-align: center;">
            <!-- Header -->
            <h1 style="margin: 0;">Company Name</h1>
        </div>
        <div style="padding: 20px; background-color: #ffffff;">
            <!-- Email Content -->
            <p>This is the content of the email.</p>
        </div>
        <div style="background-color: #f7f7f7; padding: 20px; text-align: center; font-size: 12px; color: #999999;">
            <!-- Footer -->
            <p>&copy; 2023 Company Name. All rights reserved.</p>
            <p>Contact: info@company.com | Phone: 123-456-7890</p>
        </div>
    </div>' ,
            'status' => 4
        ]);

        EmailTemplates::query()->updateOrCreate([
            'name' => 'Company Email Template 2' ,] , [
            'slug' => 'COMPANY_EMAIL_2' ,
            'subject' => 'Your Message from Company Name' ,
            'body' => '<div style="width: 100%; max-width: 600px; margin: 0 auto;">
        <div style="background-color: #f2f2f2; padding: 20px; text-align: center;">
            <!-- Logo -->
            <img src="logo.png" alt="Company Logo" style="max-width: 200px;">
        </div>
        <div style="padding: 20px; background-color: #ffffff;">
            <!-- Email Content -->
            <h1 style="margin-top: 0;">Dear [Recipient],</h1>
            <p>This is to inform you about the latest updates and news from Company Name.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam at tellus sed lectus dapibus
                fermentum.
                Donec ut hendrerit magna, eu tempus neque.</p>
            <p>Thank you for your continued support.</p>
            <p>Best regards,<br>John Doe<br>Company Name</p>
        </div>
        <div style="background-color: #f2f2f2; padding: 20px; text-align: center;">
            <!-- Footer -->
            <p>&copy; 2023 Company Name. All rights reserved.</p>
            <p>Contact: info@company.com | Phone: 123-456-7890</p>
        </div>
    </div>' ,
            'status' => 4
        ]);

        EmailTemplates::query()->updateOrCreate([
            'name' => 'Company Email Template 3' ,] , [
            'slug' => 'COMPANY_EMAIL_3' ,
            'subject' => 'Your Message from Company Name' ,
            'body' => '    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
        <tbody><tr>
            <td style="background-color: #f7f7f7; padding: 20px; text-align: center;">
                <!-- Logo -->
                <img src="logo.png" alt="Company Logo" style="max-width: 200px;">
            </td>
        </tr>
        <tr>
            <td style="background-color: #ffffff; padding: 40px;">
                <!-- Email Content -->
                <h1 style="margin-top: 0; font-size: 24px; color: #333333;">Dear [Recipient],</h1>
                <p style="margin-bottom: 20px; font-size: 16px; color: #666666;">This is to inform you about the
                    latest updates and news from Company Name.</p>
                <p style="margin-bottom: 20px; font-size: 16px; color: #666666;">Lorem ipsum dolor sit amet,
                    consectetur adipiscing elit. Nullam at tellus sed lectus dapibus fermentum. Donec ut hendrerit magna,
                    eu tempus neque.</p>
                <p style="margin-bottom: 20px; font-size: 16px; color: #666666;">Thank you for your continued support.
                </p>
                <p style="font-size: 16px; color: #666666;">Best regards,<br>John Doe<br>Company Name</p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f7f7f7; padding: 20px; text-align: center;">
                <!-- Footer -->
                <p style="margin: 0; font-size: 12px; color: #999999;">© 2023 Company Name. All rights reserved.
                </p>
                <p style="margin: 0; font-size: 12px; color: #999999;">Contact: info@company.com | Phone:
                    123-456-7890</p>
            </td>
        </tr>
    </tbody></table>' ,
            'status' => 4
        ]);
    }
}

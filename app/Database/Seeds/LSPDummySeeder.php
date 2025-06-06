<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class LSPDummySeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('id_ID');
        $db = \Config\Database::connect();

        // USERS
        $roles = ['admin', 'asesi', 'asesor'];
        $userIds = [];
        foreach ($roles as $role) {
            for ($i = 1; $i <= 3; $i++) {
                $email = $role . $i . '@example.com';
                $userId = $db->table('users')->insert([
                    'email' => $email,
                    'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                    'nama_lengkap' => ucfirst($role) . ' ' . $i,
                    'role' => $role,
                    'google_id' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], true);
                $userIds[$role][] = $db->insertID();
            }
        }

        // ADMIN
        foreach ($userIds['admin'] as $id) {
            $db->table('admin')->insert([
                'id_user' => $id,
                'jabatan' => $faker->jobTitle,
            ]);
        }

        // ASESOR
        foreach ($userIds['asesor'] as $id) {
            $db->table('asesor')->insert([
                'id_user' => $id,
                'nomor_registrasi' => $faker->numerify('REG#####'),
                'bidang_kompetensi' => $faker->word,
            ]);
        }

        // ASESII
        foreach ($userIds['asesi'] as $id) {
            $db->table('asesi')->insert([
                'id_user' => $id,
                'nik' => $faker->nik(),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '-20 years'),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'pendidikan_terakhir' => $faker->randomElement(['SMA', 'SMK', 'D3', 'S1']),
                'no_hp' => $faker->phoneNumber,
            ]);
        }

        // SKEMA
        for ($i = 1; $i <= 3; $i++) {
            $db->table('skema')->insert([
                'nama_skema' => 'Skema ' . $i,
                'kode_skema' => 'SKM' . $i,
            ]);
        }

        // PENGAJUAN_ASESMEN
        $asesiIds = $db->table('asesi')->select('id_asesi')->get()->getResultArray();
        $asesorIds = $db->table('asesor')->select('id_asesor')->get()->getResultArray();
        $skemaIds = $db->table('skema')->select('id_skema')->get()->getResultArray();
        foreach ($asesiIds as $i => $asesi) {
            $db->table('pengajuan_asesmen')->insert([
                'id_asesi' => $asesi['id_asesi'],
                'id_asesor' => $asesorIds[$i % count($asesorIds)]['id_asesor'],
                'id_skema' => $skemaIds[$i % count($skemaIds)]['id_skema'],
                'status_pengajuan' => $faker->randomElement(['pending', 'diterima', 'ditolak', 'selesai']),
                'tanggal_pengajuan' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'status_kompetensi' => $faker->randomElement(['kompeten', 'belum_kompeten']),
            ]);
        }

        // ASSESSMENT_RESULT
        $pengajuanIds = $db->table('pengajuan_asesmen')->select('id_pengajuan')->get()->getResultArray();
        foreach ($pengajuanIds as $pengajuan) {
            $db->table('assessment_result')->insert([
                'id_pengajuan' => $pengajuan['id_pengajuan'],
                'total_nilai' => $faker->randomFloat(2, 60, 100),
                'rekomendasi' => $faker->sentence,
                'status_kompetensi' => $faker->randomElement(['kompeten', 'belum_kompeten']),
                'tanggal_asesmen' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
            ]);
        }

        // DOKUMEN_PENDUKUNG
        foreach ($pengajuanIds as $pengajuan) {
            foreach (['ktp', 'ijazah', 'foto'] as $tipe) {
                $db->table('dokumen_pendukung')->insert([
                    'id_pengajuan' => $pengajuan['id_pengajuan'],
                    'tipe_dokumen' => $tipe,
                    'file_path' => 'dummy/' . $faker->uuid . '.pdf',
                ]);
            }
        }
    }
}

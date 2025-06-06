<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RestructureLSPDatabase extends Migration
{
    public function up()
    {
        // DROP TABLES IF EXIST (bersihkan dulu)
        foreach (
            [
                'dokumen_pendukung',
                'assessment_result',
                'pengajuan_asesmen',
                'skema',
                'asesi',
                'asesor',
                'admin',
                'users',
            ] as $table
        ) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        // USERS TABLE
        $this->forge->addField([
            'id_user' => ['type' => 'INT', 'auto_increment' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'nama_lengkap' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role' => ['type' => 'ENUM', 'constraint' => ['admin', 'asesi', 'asesor']],
            'google_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('users', true);

        // ADMIN TABLE
        $this->forge->addField([
            'id_admin' => ['type' => 'INT', 'auto_increment' => true],
            'id_user' => ['type' => 'INT'],
            'jabatan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addKey('id_admin', true);
        $this->forge->addForeignKey('id_user', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admin', true);

        // ASESOR TABLE
        $this->forge->addField([
            'id_asesor' => ['type' => 'INT', 'auto_increment' => true],
            'id_user' => ['type' => 'INT'],
            'nomor_registrasi' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'bidang_kompetensi' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addKey('id_asesor', true);
        $this->forge->addForeignKey('id_user', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asesor', true);

        // ASESII TABLE
        $this->forge->addField([
            'id_asesi' => ['type' => 'INT', 'auto_increment' => true],
            'id_user' => ['type' => 'INT'],
            'nik' => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'tempat_lahir' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tanggal_lahir' => ['type' => 'DATE', 'null' => true],
            'jenis_kelamin' => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'null' => true],
            'pendidikan_terakhir' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'no_hp' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ]);
        $this->forge->addKey('id_asesi', true);
        $this->forge->addForeignKey('id_user', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asesi', true);

        // SKEMA TABLE
        $this->forge->addField([
            'id_skema' => ['type' => 'INT', 'auto_increment' => true],
            'nama_skema' => ['type' => 'VARCHAR', 'constraint' => 255],
            'kode_skema' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $this->forge->addKey('id_skema', true);
        $this->forge->createTable('skema', true);

        // PENGAJUAN_ASESMEN TABLE
        $this->forge->addField([
            'id_pengajuan' => ['type' => 'INT', 'auto_increment' => true],
            'id_asesi' => ['type' => 'INT'],
            'id_asesor' => ['type' => 'INT', 'null' => true],
            'id_skema' => ['type' => 'INT'],
            'status_pengajuan' => ['type' => 'ENUM', 'constraint' => ['pending', 'diterima', 'ditolak', 'selesai'], 'default' => 'pending'],
            'tanggal_pengajuan' => ['type' => 'DATETIME', 'null' => true],
            'status_kompetensi' => ['type' => 'ENUM', 'constraint' => ['kompeten', 'belum_kompeten'], 'null' => true],
        ]);
        $this->forge->addKey('id_pengajuan', true);
        $this->forge->addForeignKey('id_asesi', 'asesi', 'id_asesi', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_asesor', 'asesor', 'id_asesor', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_skema', 'skema', 'id_skema', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengajuan_asesmen', true);

        // ASSESSMENT_RESULT TABLE
        $this->forge->addField([
            'id_result' => ['type' => 'INT', 'auto_increment' => true],
            'id_pengajuan' => ['type' => 'INT'],
            'total_nilai' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'rekomendasi' => ['type' => 'TEXT', 'null' => true],
            'status_kompetensi' => ['type' => 'ENUM', 'constraint' => ['kompeten', 'belum_kompeten'], 'null' => true],
            'tanggal_asesmen' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_result', true);
        $this->forge->addForeignKey('id_pengajuan', 'pengajuan_asesmen', 'id_pengajuan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('assessment_result', true);

        // DOKUMEN_PENDUKUNG TABLE
        $this->forge->addField([
            'id_dokumen' => ['type' => 'INT', 'auto_increment' => true],
            'id_pengajuan' => ['type' => 'INT'],
            'tipe_dokumen' => ['type' => 'ENUM', 'constraint' => ['ktp', 'ijazah', 'foto', 'lainnya']],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id_dokumen', true);
        $this->forge->addForeignKey('id_pengajuan', 'pengajuan_asesmen', 'id_pengajuan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dokumen_pendukung', true);
    }

    public function down()
    {
        $this->forge->dropTable('dokumen_pendukung', true);
        $this->forge->dropTable('assessment_result', true);
        $this->forge->dropTable('pengajuan_asesmen', true);
        $this->forge->dropTable('skema', true);
        $this->forge->dropTable('asesi', true);
        $this->forge->dropTable('asesor', true);
        $this->forge->dropTable('admin', true);
        $this->forge->dropTable('users', true);
    }
}

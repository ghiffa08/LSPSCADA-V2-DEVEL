-- Database Index Optimization for RekamanAsesmen
ALTER TABLE `rekaman_asesmen` ADD INDEX `idx_apl1` (`id_apl1`);
ALTER TABLE `rekaman_asesmen_kompetensi` ADD INDEX `idx_rekaman_unit` (`id_rekaman`, `id_unit`);
ALTER TABLE `pengajuan_asesmen` ADD INDEX `idx_asesi_asesmen` (`id_asesi`, `id_asesmen`);

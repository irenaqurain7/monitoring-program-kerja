# PRD.md --- Sistem Program Kerja, Target Unit, dan Two-Way Communication

## 1. Informasi Produk

-   **Nama produk:** Sistem Program Kerja dan Komunikasi Antarunit
    Kampus
-   **Jenis aplikasi:** Aplikasi web internal
-   **Pengguna utama:** Kepala Kampus, Master Admin, Unit Bagian, dan
    Tim
-   **Tujuan utama:** Membantu kampus mengelola unit dan tim, menyusun
    program kerja tahunan yang dapat dipantau per bulan, membuat
    laporan, serta menyediakan saluran komunikasi dua arah untuk
    menyampaikan aspirasi, kendala, dan pengaduan.

## 2. Latar Belakang

Setiap unit bagian di kampus memiliki program kerja dan target yang
perlu dicapai dalam satu tahun. Target tahunan perlu dipecah menjadi
target bulanan agar perkembangan dapat dipantau secara berkala, bukan
hanya dievaluasi pada akhir tahun.

Selain itu, setiap unit membutuhkan ruang komunikasi yang mudah diakses
untuk menyampaikan kendala, masukan, atau hal yang perlu ditindaklanjuti
oleh Kepala Kampus maupun pihak terkait. Karena itu, sistem perlu
menyediakan fitur **Two-Way Communication** yang mendukung komunikasi
dua arah dan pencatatan tindak lanjut.

Kepala Kampus berperan sebagai **Master Admin** yang dapat menambahkan
unit bagian, tim, dan pengguna serta mengelola pengaturan dasar sistem.

## 3. Tujuan Produk

1.  Memusatkan data unit bagian, tim, dan pengguna dalam satu sistem.
2.  Memfasilitasi penyusunan program kerja dan target tahunan setiap
    unit.
3.  Memungkinkan target tahunan dirinci menjadi target bulanan yang
    terukur.
4.  Menampilkan progres target bulanan dan tahunan secara jelas.
5.  Menyediakan laporan berkala untuk evaluasi kinerja unit.
6.  Menyediakan kanal Two-Way Communication untuk aspirasi, kendala, dan
    pengaduan.
7.  Memberikan Kepala Kampus kendali untuk menambah atau mengelola unit
    dan tim.
8.  Mendukung login berdasarkan username, password, dan pilihan kampus.

## 4. Peran dan Hak Akses

### 4.1 Kepala Kampus --- Master Admin

Kepala Kampus memiliki hak akses tertinggi pada kampus yang dikelolanya.

-   Login menggunakan username dan password serta memilih kampus.
-   Menambahkan, mengubah, mengaktifkan, atau menonaktifkan unit bagian.
-   Menambahkan dan mengelola tim pada setiap unit bagian.
-   Membuat dan mengelola akun pengguna.
-   Menghubungkan pengguna dengan kampus, unit bagian, dan tim.
-   Melihat seluruh program kerja dan target unit pada kampus yang
    dikelolanya.
-   Memantau progres target bulanan dan tahunan.
-   Melihat serta mengunduh laporan.
-   Menerima, menanggapi, dan mengarahkan aspirasi atau pengaduan
    melalui Two-Way Communication.
-   Melihat status tindak lanjut setiap aduan.
-   Mengelola pengaturan dasar sistem sesuai hak akses.

### 4.2 Kepala Unit / Koordinator Unit

-   Melihat data dan program kerja unitnya.
-   Menyusun atau mengajukan program kerja tahunan dan target bulanan.
-   Memperbarui progres target berdasarkan realisasi.
-   Menambahkan catatan, bukti, atau dokumen pendukung.
-   Melihat laporan unitnya sendiri.
-   Membuat topik komunikasi atau aduan dan membalas tanggapan.
-   Memantau status tindak lanjut aduan yang dibuat.
-   Tidak dapat mengubah unit lain atau mengelola akun Master Admin.

### 4.3 Anggota Tim / Pengguna Unit

-   Melihat program kerja dan target yang relevan dengan unit atau
    timnya.
-   Memperbarui progres apabila diberi izin oleh Kepala Unit.
-   Menyampaikan aspirasi, kendala, atau pengaduan melalui Two-Way
    Communication.
-   Melihat balasan dan status tindak lanjut untuk laporan yang
    dibuatnya.
-   Tidak dapat melihat aduan privat milik pengguna lain kecuali diberi
    kewenangan.

> Hak akses rinci dapat disesuaikan saat implementasi. Sistem wajib
> membatasi data berdasarkan kampus, unit, tim, dan peran pengguna.

## 5. Modul dan Kebutuhan Fungsional

### 5.1 Login dan Pemilihan Kampus

Halaman login menyediakan: - Kolom **Username**. - Kolom **Password**. -
Dropdown **Pilih Kampus**. - Tombol **Masuk**. - Validasi jika username,
password, atau kampus tidak sesuai. - Pesan kesalahan yang jelas tanpa
membocorkan informasi sensitif. - Fitur logout dan pengelolaan sesi
pengguna.

**Aturan:** Pengguna hanya dapat mengakses data kampus yang sesuai
dengan akun dan hak aksesnya. Pemilihan kampus pada form login tidak
boleh menjadi cara untuk melewati pembatasan akses.

### 5.2 Master Data Unit Bagian

Master Admin dapat: - Menambahkan unit bagian baru. - Mengubah nama dan
deskripsi unit. - Menentukan penanggung jawab unit. - Menambahkan atau
menghubungkan tim ke unit. - Mengaktifkan atau menonaktifkan unit. -
Melihat daftar unit beserta jumlah tim dan anggota.

Contoh unit yang dapat disiapkan sebagai data awal: - Marketing /
Marcom. - Administrasi dan Sumber Daya. - Kemahasiswaan. - BSI Career
Center. - BSI Entrepreneur Center. - Technical Support. - Unit lain yang
ditambahkan oleh Kepala Kampus.

Nama di atas merupakan contoh dari catatan awal dan harus bisa diubah
atau ditambah sesuai struktur organisasi kampus.

### 5.3 Master Data Tim dan Pengguna

-   Master Admin dapat membuat tim di dalam unit bagian.
-   Master Admin dapat membuat akun pengguna dengan username dan
    password awal.
-   Akun pengguna dikaitkan dengan kampus, unit, tim, dan peran.
-   Sistem harus mencegah username ganda sesuai kebijakan identitas
    akun.
-   Pengguna yang dinonaktifkan tidak dapat login.
-   Perubahan unit atau tim dicatat dalam riwayat aktivitas bila
    memungkinkan.

### 5.4 Program Kerja Tahunan

Setiap unit bagian dapat memiliki satu atau lebih program kerja.

Data program kerja minimal: - Nama program kerja. - Deskripsi dan
tujuan. - Unit penanggung jawab. - Penanggung jawab program. - Tahun
periode program. - Target tahunan. - Satuan target (misalnya kegiatan,
peserta, dokumen, persen, atau nilai). - Indikator keberhasilan. -
Jadwal mulai dan selesai. - Status program: Draft, Diajukan, Disetujui,
Berjalan, Selesai, atau Ditunda. - Catatan dan dokumen pendukung.

Alur program kerja: 1. Kepala Unit membuat program kerja tahunan. 2.
Kepala Unit menentukan target tahunan dan indikator keberhasilan. 3.
Target tahunan dibagi ke dalam target bulanan. 4. Program diajukan untuk
ditinjau sesuai alur persetujuan kampus. 5. Setelah disetujui, program
dapat dipantau dan diperbarui progresnya. 6. Perubahan target setelah
persetujuan harus memiliki catatan perubahan.

### 5.5 Target Bulanan dan Pemantauan Progres

Target tahunan harus dapat diuraikan menjadi target untuk
Januari--Desember. Pembagian target tidak harus sama setiap bulan;
pengguna dapat menyesuaikannya dengan jadwal kegiatan.

Contoh: - Target tahunan: 120 kegiatan. - Januari: 8 kegiatan. -
Februari: 10 kegiatan. - Maret: 12 kegiatan. - Bulan berikutnya:
disesuaikan hingga total target tahunan tercapai.

Fitur yang dibutuhkan: - Input target per bulan. - Input realisasi
bulanan. - Perhitungan persentase capaian bulanan. - Perhitungan total
realisasi dan capaian tahunan. - Visualisasi target dibandingkan
realisasi. - Status capaian: Belum Dimulai, Berjalan, Tercapai, atau
Perlu Perhatian. - Catatan kendala dan rencana tindak lanjut. - Unggah
bukti realisasi, seperti foto, dokumen, atau tautan. - Riwayat pembaruan
target dan realisasi.

**Rumus dasar:** - Capaian bulanan (%) = realisasi bulanan ÷ target
bulanan × 100%. - Capaian tahunan (%) = total realisasi tahun berjalan ÷
target tahunan × 100%.

Jika target bernilai nol atau belum ditentukan, sistem tidak boleh
membagi dengan nol dan harus menampilkan status yang sesuai. Untuk
target berbentuk persentase atau nilai yang tidak dapat dijumlahkan
langsung, perhitungan perlu mengikuti jenis indikatornya.

### 5.6 Dashboard

Dashboard menampilkan informasi sesuai peran pengguna.

**Dashboard Kepala Kampus / Master Admin:** - Jumlah unit bagian dan tim
aktif. - Jumlah program kerja tahun berjalan. - Ringkasan capaian target
tahunan seluruh unit. - Grafik target dan realisasi per bulan. -
Perbandingan capaian antarunit. - Program yang tertinggal dari target. -
Program yang mendekati tenggat. - Ringkasan laporan dan aduan Two-Way
Communication yang belum selesai.

**Dashboard Kepala Unit:** - Ringkasan program kerja unit. - Target dan
realisasi bulan berjalan. - Capaian kumulatif tahunan. - Daftar target
yang perlu perhatian. - Status pengajuan program. - Aduan atau
komunikasi yang sedang ditindaklanjuti.

**Dashboard Anggota Tim:** - Program kerja yang relevan. - Tugas atau
target yang diberikan. - Progres dan tenggat yang perlu diperhatikan. -
Komunikasi atau aduan miliknya beserta statusnya.

### 5.7 Laporan

Sistem menyediakan laporan berdasarkan: - Periode bulanan. - Periode
tahunan. - Rentang tanggal, jika diperlukan. - Kampus. - Unit bagian. -
Tim. - Program kerja. - Status program dan capaian.

Isi laporan minimal: - Nama unit dan program kerja. - Target tahunan. -
Target setiap bulan. - Realisasi setiap bulan. - Persentase capaian
bulanan dan tahunan. - Status program. - Kendala dan tindak lanjut. -
Catatan serta bukti pendukung.

Laporan dapat ditampilkan di aplikasi dan disediakan untuk ekspor PDF
atau Excel. Data laporan harus mengikuti hak akses pengguna. Kepala
Kampus dapat melihat rekap lintas unit pada kampus yang dikelolanya,
sedangkan Kepala Unit hanya dapat melihat data unitnya kecuali diberikan
akses tambahan.

### 5.8 Two-Way Communication

Modul ini menjadi sarana komunikasi dua arah antara anggota tim, Kepala
Unit, dan Kepala Kampus. Pengguna dapat menyampaikan unek-unek, masukan,
kendala pekerjaan, atau pengaduan secara langsung melalui sistem.

**Fitur utama:** - Membuat topik komunikasi atau aduan baru. - Memilih
kategori, misalnya Aspirasi, Kendala Pekerjaan, Fasilitas, Koordinasi,
atau Lainnya. - Menulis judul dan uraian masalah. - Memilih tingkat
prioritas: Rendah, Sedang, Tinggi, atau Mendesak. - Menentukan apakah
aduan bersifat terbuka sesuai kewenangan atau privat. - Melampirkan file
atau gambar jika diperlukan. - Mengirim dan menerima balasan dalam satu
utas percakapan. - Menampilkan tanggal, waktu, pengirim, dan riwayat
balasan. - Menetapkan penanggung jawab tindak lanjut. - Mengubah status:
Baru, Dalam Tinjauan, Diproses, Menunggu Informasi, Selesai, atau
Ditutup. - Memberi notifikasi saat ada balasan atau perubahan status. -
Menyimpan riwayat tindakan dan penyelesaian. - Memungkinkan pembuat
aduan mengonfirmasi apakah masalah telah ditangani.

**Alur komunikasi:** 1. Pengguna mengirim aspirasi, kendala, atau
pengaduan. 2. Sistem mencatat laporan dan memberi nomor referensi. 3.
Pihak berwenang menerima notifikasi. 4. Kepala Kampus atau pihak yang
ditunjuk meninjau dan memberikan tanggapan. 5. Pengguna dapat membalas
atau memberikan informasi tambahan. 6. Penanggung jawab memperbarui
status dan mencatat tindak lanjut. 7. Aduan ditandai selesai setelah
tindak lanjut dilakukan; pembuat aduan dapat memberikan konfirmasi atau
catatan. 8. Riwayat percakapan tetap tersimpan agar dapat ditelusuri.

**Privasi dan keamanan komunikasi:** - Aduan privat hanya dapat dilihat
pembuat aduan dan pihak yang diberi wewenang. - Jangan menampilkan aduan
sensitif pada dashboard publik atau rekap umum. - Sistem perlu membatasi
siapa yang dapat melihat, membalas, dan mengubah status aduan. -
Perubahan status dan penanggung jawab dicatat dalam log aktivitas. -
Sediakan penjelasan bahwa kanal ini merupakan sarana internal dan bukan
pengganti layanan darurat.

### 5.9 Notifikasi

Sistem dapat memberikan notifikasi di dalam aplikasi untuk: - Program
kerja yang diajukan atau disetujui. - Tenggat program yang mendekat. -
Target bulanan yang belum diperbarui. - Target yang tertinggal dari
rencana. - Aduan baru yang perlu ditinjau. - Balasan baru pada
percakapan. - Perubahan status atau penanggung jawab aduan.

Notifikasi email atau WhatsApp dapat dipertimbangkan sebagai
pengembangan lanjutan.

### 5.10 Referensi dan Riwayat Aktivitas

Sistem mencatat aktivitas penting, seperti: - Penambahan atau perubahan
unit dan tim. - Pembuatan dan perubahan program kerja. - Perubahan
target dan realisasi. - Persetujuan program. - Perubahan status aduan
dan balasan. - Perubahan hak akses pengguna.

Log aktivitas hanya dapat dilihat oleh peran yang berwenang.

## 6. Kebutuhan Nonfungsional

### 6.1 Keamanan

-   Password disimpan dalam bentuk hash, bukan teks biasa.
-   Validasi input pada sisi server.
-   Proteksi terhadap akses tanpa izin.
-   Pembatasan data berdasarkan kampus, unit, tim, dan peran.
-   Pengelolaan sesi login yang aman.
-   Validasi tipe dan ukuran file unggahan.
-   Log aktivitas untuk tindakan penting.

### 6.2 Kemudahan Penggunaan

-   Antarmuka sederhana, konsisten, dan mudah dipahami.
-   Navigasi utama melalui sidebar.
-   Menu dikelompokkan berdasarkan Dashboard, Organisasi, Program Kerja,
    Target & Realisasi, Laporan, dan Two-Way Communication.
-   Tabel memiliki pencarian, filter, pengurutan, dan pagination bila
    diperlukan.
-   Tampilan responsif pada desktop, tablet, dan ponsel.

### 6.3 Kinerja dan Keandalan

-   Halaman utama dan dashboard harus tetap responsif untuk jumlah data
    kampus yang wajar.
-   Proses simpan dan perubahan status menampilkan hasil yang jelas.
-   Kegagalan proses menampilkan pesan yang mudah dipahami.
-   Data penting perlu dicadangkan secara berkala sesuai kebijakan
    pengelola.

### 6.4 Konsistensi Data

-   Target bulanan harus terhubung dengan program kerja dan tahun yang
    benar.
-   Sistem mencegah realisasi tercatat pada program yang tidak sesuai.
-   Perubahan target setelah persetujuan memiliki jejak perubahan.
-   Data lintas kampus tidak boleh tercampur.

## 7. Rancangan Navigasi

Contoh struktur sidebar:

-   Dashboard
-   Organisasi
    -   Unit Bagian
    -   Tim
    -   Pengguna
-   Program Kerja
    -   Daftar Program
    -   Pengajuan Program
    -   Persetujuan Program
-   Target & Realisasi
    -   Target Bulanan
    -   Progres Tahunan
    -   Bukti Realisasi
-   Laporan
    -   Laporan Bulanan
    -   Laporan Tahunan
    -   Rekap per Unit
-   Two-Way Communication
    -   Kotak Masuk
    -   Buat Aduan / Aspirasi
    -   Aduan Saya
    -   Tindak Lanjut
-   Pengaturan
    -   Profil
    -   Pengaturan Akun

Menu yang ditampilkan harus menyesuaikan peran pengguna.

## 8. Entitas Data Utama

Entitas berikut merupakan rancangan awal dan dapat disesuaikan saat
desain basis data.

1.  **Kampus** --- ID, nama kampus, kode kampus, status.
2.  **Pengguna** --- ID, nama, username, password hash, peran, kampus,
    status.
3.  **Unit Bagian** --- ID, kampus, nama unit, deskripsi, penanggung
    jawab, status.
4.  **Tim** --- ID, unit, nama tim, penanggung jawab, status.
5.  **Keanggotaan** --- pengguna, unit, tim, tanggal mulai, status.
6.  **Program Kerja** --- ID, unit, nama, deskripsi, tahun, target
    tahunan, satuan, indikator, status, penanggung jawab.
7.  **Target Bulanan** --- program kerja, bulan, target, realisasi,
    persentase capaian, catatan.
8.  **Bukti Realisasi** --- target bulanan, nama file atau URL,
    pengunggah, tanggal unggah.
9.  **Persetujuan Program** --- program, pemberi persetujuan, keputusan,
    catatan, waktu.
10. **Aduan / Topik Komunikasi** --- ID referensi, pembuat, kampus,
    kategori, judul, isi awal, prioritas, tingkat privasi, penanggung
    jawab, status, tanggal dibuat.
11. **Pesan Komunikasi** --- aduan, pengirim, isi pesan, lampiran,
    tanggal dibuat.
12. **Riwayat Status Aduan** --- aduan, status sebelumnya, status baru,
    pelaku, catatan, waktu.
13. **Notifikasi** --- penerima, jenis, referensi, status dibaca, waktu.
14. **Log Aktivitas** --- pengguna, tindakan, objek, waktu, dan
    informasi perubahan yang relevan.

## 9. Aturan Bisnis

1.  Setiap program kerja wajib memiliki unit penanggung jawab dan tahun
    periode.
2.  Program kerja tahunan dapat memiliki maksimal 12 target bulanan
    untuk satu tahun kalender.
3.  Total target bulanan sebaiknya sesuai dengan target tahunan untuk
    indikator yang bersifat akumulatif. Jika berbeda, sistem menampilkan
    peringatan dan meminta konfirmasi.
4.  Realisasi bulanan tidak boleh diubah tanpa hak akses yang sesuai.
5.  Program yang sudah disetujui tetap dapat diperbarui, tetapi
    perubahan target harus tercatat.
6.  Master Admin hanya mengelola data organisasi pada kampus yang
    menjadi kewenangannya, kecuali sistem memang dirancang untuk admin
    lintas kampus.
7.  Pengguna hanya melihat data program dan komunikasi sesuai hak
    aksesnya.
8.  Aduan privat hanya dapat diakses oleh pihak yang berwenang.
9.  Status aduan tidak boleh langsung hilang setelah selesai; seluruh
    riwayat tetap tersedia.
10. Sistem tidak boleh menganggap aduan selesai hanya karena telah
    dibalas; perlu ada status tindak lanjut yang jelas.
11. Perhitungan capaian harus mempertimbangkan jenis indikator dan
    menghindari pembagian dengan nol.
12. Periode laporan harus konsisten dengan tahun dan bulan program
    kerja.

## 10. Kriteria Penerimaan

Sistem dianggap memenuhi kebutuhan awal apabila:

-   [ ] Pengguna dapat login menggunakan username, password, dan pilihan
    kampus.
-   [ ] Kepala Kampus sebagai Master Admin dapat menambah, mengubah, dan
    menonaktifkan unit bagian.
-   [ ] Master Admin dapat menambahkan tim dan menghubungkan pengguna ke
    unit/tim.
-   [ ] Kepala Unit dapat membuat program kerja tahunan.
-   [ ] Program kerja dapat memiliki target untuk setiap bulan.
-   [ ] Pengguna berwenang dapat memasukkan realisasi bulanan dan bukti
    pendukung.
-   [ ] Sistem menampilkan capaian bulanan serta kumulatif tahunan.
-   [ ] Dashboard menampilkan ringkasan yang sesuai dengan peran
    pengguna.
-   [ ] Laporan bulanan dan tahunan dapat difilter berdasarkan unit dan
    periode.
-   [ ] Pengguna dapat membuat aspirasi atau aduan melalui Two-Way
    Communication.
-   [ ] Pihak berwenang dapat membalas, menetapkan penanggung jawab, dan
    memperbarui status aduan.
-   [ ] Pengguna dapat melihat riwayat komunikasi dan tindak lanjut
    aduannya sendiri.
-   [ ] Aduan privat tidak terlihat oleh pengguna yang tidak berwenang.
-   [ ] Data antarunit dan antarkampus terbatasi sesuai hak akses.
-   [ ] Perubahan target, status aduan, dan aktivitas penting tercatat.

## 11. Prioritas Pengembangan

### MVP --- Tahap Pertama

1.  Login, logout, dan pemilihan kampus.
2.  Manajemen pengguna, unit bagian, dan tim.
3.  Pembuatan program kerja tahunan.
4.  Input target dan realisasi bulanan.
5.  Dashboard progres target.
6.  Laporan bulanan dan tahunan.
7.  Two-Way Communication dengan percakapan, status, dan pembatasan
    akses dasar.

### Tahap Berikutnya

1.  Notifikasi email atau WhatsApp.
2.  Ekspor laporan lanjutan dan penjadwalan laporan otomatis.
3.  Grafik perbandingan lintas periode.
4.  Persetujuan berjenjang yang dapat dikonfigurasi.
5.  Analisis tren capaian dan pengingat otomatis.
6.  Survei kepuasan atas tindak lanjut komunikasi.

## 12. Asumsi dan Hal yang Perlu Dikonfirmasi

-   Apakah setiap Kepala Kampus hanya mengelola satu kampus atau dapat
    mengelola beberapa kampus?
-   Apakah program kerja harus disetujui Kepala Kampus sebelum berjalan?
-   Apakah semua unit menggunakan indikator kuantitatif, atau ada target
    kualitatif yang perlu dinilai dengan metode berbeda?
-   Apakah anggota tim boleh mengirim aduan secara anonim, atau
    identitas harus selalu terlihat oleh penerima?
-   Apakah aduan dapat langsung dikirim kepada Kepala Kampus, atau harus
    melalui Kepala Unit terlebih dahulu?
-   Apakah laporan perlu diekspor ke PDF, Excel, atau keduanya?
-   Apakah notifikasi cukup di dalam aplikasi untuk tahap pertama?

## 13. Arah Desain UI/UX

-   Gaya profesional, rapi, modern, dan mudah digunakan untuk kebutuhan
    internal kampus.
-   Gunakan warna dasar putih dengan aksen biru/navy.
-   Tampilkan grafik target vs realisasi bulanan yang mudah dibaca.
-   Gunakan kartu ringkasan untuk target tahunan, capaian bulan ini,
    program berjalan, dan aduan yang belum selesai.
-   Gunakan badge status dengan warna konsisten dan tidak berlebihan.
-   Form target bulanan sebaiknya ditampilkan dalam tabel
    Januari--Desember agar mudah dibandingkan.
-   Two-Way Communication menggunakan tampilan percakapan dengan status
    dan riwayat tindak lanjut yang jelas.
-   Pastikan tampilan responsif serta aksesibilitas dasar seperti
    kontras teks, label form, dan pesan validasi.

------------------------------------------------------------------------

**Catatan:** Dokumen ini adalah PRD awal berdasarkan kebutuhan yang
disampaikan. Rincian alur persetujuan, aturan privasi aduan, dan cakupan
pengelolaan lintas kampus perlu dipastikan sebelum implementasi final.

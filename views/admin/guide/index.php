<?php
/** @var array $data */
require_once APPROOT . '/views/layouts/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-heading mb-1"><i class="bi bi-book me-2 text-primary"></i>Petunjuk Penggunaan Aplikasi
        </h4>
        <p class="text-secondary small mb-0">Panduan lengkap cara menggunakan sistem Billing ISP Management ini.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="guideAccordion">

                    <!-- 1. Pendahuluan -->
                    <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header" id="headingIntro">
                            <button class="accordion-button bg-transparent text-heading fw-bold shadow-none" type="button"
                                data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#collapseIntro"
                                aria-expanded="true" aria-controls="collapseIntro">
                                <i class="bi bi-info-circle text-info me-2"></i> 1. Pendahuluan & Dashboard Utama
                            </button>
                        </h2>
                        <div id="collapseIntro" class="accordion-collapse collapse show" aria-labelledby="headingIntro"
                            data-bs-parent="#guideAccordion">
                            <div class="accordion-body text-secondary small">
                                <p>Selamat datang di <strong>Billing App - ISP Management</strong>. Aplikasi ini
                                    dirancang untuk memudahkan manajemen pelanggan internet, sinkronisasi dengan router
                                    MikroTik, dan pembuatan tagihan otomatis.</p>
                                <ul>
                                    <li><strong>Dashboard Utama:</strong> Memberikan ringkasan cepat mengenai total
                                        pelanggan (aktif/nonaktif/terisolir), pendapatan bulan ini, tagihan belum lunas,
                                        dan status router terkini.</li>
                                    <li><strong>Sistem Real-time:</strong> Data di dashboard akan diperbarui secara
                                        otomatis setiap beberapa detik tanpa perlu memuat ulang (refresh) halaman.</li>
                                    <li><strong>Tema Terang / Gelap:</strong> Anda dapat mengganti tema tampilan
                                        menggunakan tombol di bagian bawah sidebar menu.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Manajemen Router -->
                    <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header" id="headingRouter">
                            <button class="accordion-button collapsed bg-transparent text-heading fw-bold shadow-none"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseRouter"
                                aria-expanded="false" aria-controls="collapseRouter">
                                <i class="bi bi-router text-success me-2"></i> 2. Mengelola Router (MikroTik)
                            </button>
                        </h2>
                        <div id="collapseRouter" class="accordion-collapse collapse" aria-labelledby="headingRouter"
                            data-bs-parent="#guideAccordion">
                            <div class="accordion-body text-secondary small">
                                <p>Aplikasi ini dapat terhubung secara langsung ke router MikroTik Anda menggunakan
                                    fitur API.</p>
                                <ol>
                                    <li>Masuk ke menu <strong>Router / Server</strong>.</li>
                                    <li>Klik tombol <strong>Tambah Router</strong>.</li>
                                    <li>Isikan detail router seperti Nama, IP Host, Port API (biasanya 8728), Username,
                                        dan Password API MikroTik.</li>
                                    <li>Aplikasi akan secara otomatis mencoba menghubungkan (Ping & Test API) ke router
                                        setiap kali diakses untuk memantau status Online/Offline.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Manajemen Paket -->
                    <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header" id="headingPackage">
                            <button class="accordion-button collapsed bg-transparent text-heading fw-bold shadow-none"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapsePackage"
                                aria-expanded="false" aria-controls="collapsePackage">
                                <i class="bi bi-box-seam text-warning me-2"></i> 3. Mengelola Paket Internet
                            </button>
                        </h2>
                        <div id="collapsePackage" class="accordion-collapse collapse" aria-labelledby="headingPackage"
                            data-bs-parent="#guideAccordion">
                            <div class="accordion-body text-secondary small">
                                <p>Sebelum menambahkan pelanggan, pastikan Anda telah membuat Paket Internet.</p>
                                <ol>
                                    <li>Buka menu <strong>Paket Internet</strong>.</li>
                                    <li>Klik <strong>Tambah Paket</strong>.</li>
                                    <li>Nama paket yang Anda buat sebaiknya disamakan (mirip) dengan <em>Profile
                                            PPPoE</em> yang ada di MikroTik untuk mempermudah identifikasi.</li>
                                    <li>Tentukan harga berlangganan bulanan dari paket tersebut.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Manajemen Pelanggan & PPPoE -->
                    <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header" id="headingCustomer">
                            <button class="accordion-button collapsed bg-transparent text-heading fw-bold shadow-none"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseCustomer"
                                aria-expanded="false" aria-controls="collapseCustomer">
                                <i class="bi bi-people text-primary me-2"></i> 4. Pelanggan & Sinkronisasi PPPoE
                            </button>
                        </h2>
                        <div id="collapseCustomer" class="accordion-collapse collapse" aria-labelledby="headingCustomer"
                            data-bs-parent="#guideAccordion">
                            <div class="accordion-body text-secondary small">
                                <p>Ada dua cara untuk menambahkan pelanggan ke dalam sistem:</p>
                                <ul>
                                    <li><strong>Input Manual:</strong> Melalui menu <em>Pelanggan -> Tambah Pelanggan
                                            Baru</em>. Isi data diri lengkap pelanggan beserta paket dan router yang
                                        dipilih.</li>
                                    <li><strong>Import dari MikroTik (Otomatis):</strong> Buka menu <em>Data PPPoE
                                            (MikroTik)</em>, lalu pilih Router. Sistem akan menampilkan seluruh daftar
                                        <em>PPPoE Secret</em> di router tersebut. Anda bisa langsung membuat pelanggan
                                        baru berdasarkan data secret tersebut dengan klik tombol <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Buat
                                            Customer</span>.</li>
                                </ul>
                                <p class="mt-2 text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> Jika
                                    koneksi pelanggan diputus dari sisi tagihan (Isolir), sistem akan otomatis
                                    mendisable <em>PPPoE Secret</em> pelanggan tersebut di MikroTik secara realtime.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Tagihan & Pembayaran -->
                    <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header" id="headingBilling">
                            <button class="accordion-button collapsed bg-transparent text-heading fw-bold shadow-none"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseBilling"
                                aria-expanded="false" aria-controls="collapseBilling">
                                <i class="bi bi-receipt text-danger me-2"></i> 5. Pembuatan Tagihan (Invoice)
                            </button>
                        </h2>
                        <div id="collapseBilling" class="accordion-collapse collapse" aria-labelledby="headingBilling"
                            data-bs-parent="#guideAccordion">
                            <div class="accordion-body text-secondary small">
                                <p>Tagihan dapat di-generate secara massal maupun dikirim secara manual:</p>
                                <ul>
                                    <li><strong>Generate Otomatis:</strong> Di menu <em>Generate Tagihan</em>, Anda
                                        dapat memilih bulan dan tahun, lalu sistem akan membuatkan invoice masal untuk
                                        seluruh pelanggan aktif yang belum memiliki tagihan pada bulan tersebut.</li>
                                    <li><strong>Kirim via WhatsApp:</strong> Di menu <em>Tagihan Manual (Direct
                                            WA)</em>, Anda dapat melihat daftar tagihan bulanan dan mengirimkan pesan
                                        tagihan / pengingat secara langsung ke WhatsApp pelanggan hanya dengan sekali
                                        klik (menggunakan API WA gateway jika dikonfigurasi, atau dialihkan ke aplikasi
                                        WA langsung).</li>
                                </ul>
                                <p>Ketika pelanggan melunasi tagihan, Anda dapat memperbarui statusnya di menu detail
                                    pelanggan, dan koneksi (jika sebelumnya terisolir) akan diaktifkan (enable secret)
                                    kembali ke MikroTik secara otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Laporan -->
                    <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header" id="headingReport">
                            <button class="accordion-button collapsed bg-transparent text-heading fw-bold shadow-none"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseReport"
                                aria-expanded="false" aria-controls="collapseReport">
                                <i class="bi bi-graph-up text-info me-2"></i> 6. Laporan Keuangan
                            </button>
                        </h2>
                        <div id="collapseReport" class="accordion-collapse collapse" aria-labelledby="headingReport"
                            data-bs-parent="#guideAccordion">
                            <div class="accordion-body text-secondary small">
                                <p>Menu <strong>Laporan & Arus Kas</strong> memberikan ringkasan total pemasukan dan
                                    tunggakan secara periodik.</p>
                                <ul>
                                    <li>Anda bisa memfilter data berdasarkan bulan dan tahun spesifik.</li>
                                    <li>Sistem menyediakan fitur pencetakan laporan dalam bentuk dokumen
                                        <strong>PDF</strong> yang siap cetak.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Info -->
    <div class="col-12 col-xl-4">
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-3">
                <h6 class="text-heading fw-bold mb-0"><i class="bi bi-list-ul text-primary me-2"></i>Daftar Isi</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush bg-transparent">
                    <a href="javascript:void(0)" onclick="openAccordion('collapseIntro')" class="list-group-item list-group-item-action bg-transparent text-secondary border-secondary border-opacity-25 small py-3">
                        1. Pendahuluan & Dashboard Utama
                    </a>
                    <a href="javascript:void(0)" onclick="openAccordion('collapseRouter')" class="list-group-item list-group-item-action bg-transparent text-secondary border-secondary border-opacity-25 small py-3">
                        2. Mengelola Router (MikroTik)
                    </a>
                    <a href="javascript:void(0)" onclick="openAccordion('collapsePackage')" class="list-group-item list-group-item-action bg-transparent text-secondary border-secondary border-opacity-25 small py-3">
                        3. Mengelola Paket Internet
                    </a>
                    <a href="javascript:void(0)" onclick="openAccordion('collapseCustomer')" class="list-group-item list-group-item-action bg-transparent text-secondary border-secondary border-opacity-25 small py-3">
                        4. Pelanggan & Sinkronisasi PPPoE
                    </a>
                    <a href="javascript:void(0)" onclick="openAccordion('collapseBilling')" class="list-group-item list-group-item-action bg-transparent text-secondary border-secondary border-opacity-25 small py-3">
                        5. Pembuatan Tagihan (Invoice)
                    </a>
                    <a href="javascript:void(0)" onclick="openAccordion('collapseReport')" class="list-group-item list-group-item-action bg-transparent text-secondary border-0 small py-3">
                        6. Laporan Keuangan
                    </a>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3"
                    style="width: 60px; height: 60px;">
                    <i class="bi bi-shield-check fs-2"></i>
                </div>
                <h5 class="text-heading fw-bold">Tips Keamanan</h5>
                <p class="text-secondary small mb-0">Pastikan Anda tidak membagikan kredensial Login Admin maupun
                    kredensial akses API MikroTik kepada pihak yang tidak bertanggung jawab.</p>
            </div>
        </div>

        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="text-heading fw-bold mb-3"><i class="bi bi-telephone-outbound text-success me-2"></i> Butuh
                    Bantuan Lanjutan?</h6>
                <p class="text-secondary small mb-3">Jika mengalami kendala teknis atau masalah sinkronisasi dengan
                    MikroTik yang tidak terselesaikan dengan panduan ini, hubungi Administrator Sistem utama.</p>
                <div
                    class="d-flex align-items-center gap-2 text-info small bg-info bg-opacity-10 border border-info border-opacity-25 p-2 rounded">
                    <i class="bi bi-envelope"></i>
                    <span>nurazizan.oss@gmail.com</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Accordion custom styling for Dark/Light Theme */
    .accordion-button:not(.collapsed) {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        color: var(--bs-primary) !important;
        box-shadow: inset 0 -1px 0 var(--glass-border);
    }

    .accordion-item {
        background-color: transparent !important;
    }

    .text-heading {
        color: var(--heading-color) !important;
    }

    /* Fix accordion chevron icon color for dark mode */
    [data-theme="dark"] .accordion-button::after {
        filter: invert(1);
    }
    
    [data-theme="light"] .accordion-button::after {
        filter: none;
    }
</style>

<script>
    function openAccordion(targetId) {
        let target = document.getElementById(targetId);
        if (target) {
            let bsCollapse = bootstrap.Collapse.getInstance(target);
            if (!bsCollapse) bsCollapse = new bootstrap.Collapse(target, { toggle: false });
            bsCollapse.show();
            
            // Smooth scroll to the accordion header (slightly above it)
            setTimeout(() => {
                let header = target.previousElementSibling;
                if(header) {
                    let topPos = header.getBoundingClientRect().top + window.scrollY - 80;
                    window.scrollTo({top: topPos, behavior: 'smooth'});
                }
            }, 300);
        }
    }
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
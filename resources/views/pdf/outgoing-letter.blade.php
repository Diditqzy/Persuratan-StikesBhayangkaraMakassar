<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat - {{ $data->letter_number }}</title>
    <style>
        /* PENGATURAN KERTAS A4 PORTRAIT */
        @page { margin: 2cm 2.5cm 2.5cm 2.5cm; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
        }

        /* HELPER */
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .underline { text-decoration: underline; }
        
        /* KOP SURAT (3 KOLOM: LOGO - TEKS - LOGO) */
        .kop-table {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        .kop-table td { vertical-align: middle; }
        .logo-kiri { width: 90px; height: auto; }
        .logo-kanan { width: 90px; height: auto; }
        
        .kop-text-yayasan { font-size: 11pt; font-weight: bold; }
        .kop-text-kampus { font-size: 14pt; font-weight: bold; }
        .kop-text-singkatan { font-size: 12pt; font-weight: bold; margin-bottom: 2px; }
        .kop-text-alamat { font-size: 9pt; font-style: normal; }

        /* JUDUL SURAT */
        .judul-surat {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12pt; /* Sesuai gambar tidak terlalu besar */
            text-decoration: underline;
        }
        .nomor-surat {
            text-align: center;
            font-size: 12pt;
            margin-top: 2px;
            margin-bottom: 25px;
        }

        /* TABEL DATA (BIODATA) */
        .tabel-data {
            width: 100%;
            margin-left: 20px; /* Indentasi seperti gambar */
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .tabel-data td {
            vertical-align: top;
            padding: 2px 0;
        }
        .label-col { width: 140px; } /* Lebar label 'Nama', 'NUPTK' */
        .sep-col { width: 20px; text-align: center; }

        /* PARAGRAF */
        .paragraph {
            text-align: justify;
            text-indent: 40px; /* Menjorok ke dalam */
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .intro-text { margin-bottom: 10px; }

        /* TANDA TANGAN */
        .ttd-wrapper {
            float: right;
            width: 45%;
            text-align: center;
            margin-top: 30px;
        }
        .ttd-tanggal { margin-bottom: 5px; }
        .ttd-jabatan { font-weight: bold; margin-bottom: 60px; } /* Ruang TTD/QR */
        .ttd-nama { font-weight: bold; text-decoration: underline; }
        
        /* QR Code Style */
        .qr-box {
            height: 80px; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            margin: 5px auto;
            position: relative;
        }
        .qr-img { width: 80px; height: 80px; }
        .qr-overlay {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 20px; height: 20px;
            background: #fff;
            padding: 2px;
        }

        /* CLEANUP */
        .clear { clear: both; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            {{-- LOGO KIRI --}}
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo-ybubm.jpg') }}" class="logo-kiri" alt="Logo Yayasan">
            </td>
            
            {{-- TEKS TENGAH --}}
            <td width="70%" align="center">
                <div class="kop-text-yayasan uppercase">YAYASAN BRATA UTAMA BHAYANGKARA MAKASSAR</div>
                <div class="kop-text-kampus uppercase">SEKOLAH TINGGI ILMU KESEHATAN BHAYANGKARA MAKASSAR</div>
                <div class="kop-text-singkatan uppercase">( STIKBHARA MAKASSAR )</div>
                <div class="kop-text-alamat">
                    Jl. Letjen Pol. Mappa Oudang No. 63 Makassar 90223 HP/WA. 085824855515<br>
                    Website : www.stikbhara.ac.id &nbsp; Email : stikbhara@gmail.com
                </div>
            </td>

            {{-- LOGO KANAN --}}
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo.png') }}" class="logo-kanan" alt="Logo STIKES">
            </td>
        </tr>
    </table>

    {{-- LOGIKA: HANYA UNTUK SURAT KETERANGAN KULIAH (ID 1) --}}
    @if($data->type_id == 1)

        {{-- JUDUL --}}
        <div class="judul-surat">SURAT KETERANGAN KULIAH</div>
        <div class="nomor-surat">Nomor : &nbsp;&nbsp; {{ $data->letter_number ?? '...../SKT...../...../2025/STIKBHARA' }}</div>

        {{-- ISI: DATA PENANDATANGAN --}}
        <div class="intro-text">
            Yang bertandatangan di bawah ini &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
        </div>

        <table class="tabel-data">
            <tr>
                <td class="label-col font-bold" style="letter-spacing: 2px;">Nama</td>
                <td class="sep-col">:</td>
                <td class="font-bold">{{ $data->signer->user->name }}</td>
            </tr>
            <tr>
                <td class="label-col font-bold" style="letter-spacing: 2px;">NUPTK</td>
                <td class="sep-col">:</td>
                <td>{{ $data->signer->employee_id ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col font-bold" style="letter-spacing: 2px;">Jabatan</td>
                <td class="sep-col">:</td>
                <td class="uppercase">{{ $data->signer->position }}</td>
            </tr>
        </table>

        {{-- ISI: DATA MAHASISWA --}}
        <div class="intro-text" style="margin-top: 15px;">
            Menerangkan bahwa &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
        </div>

        {{-- Helper Variables dari additional_data --}}
        @php
            $mhs = $data->additional_data;
            $namaMhs = $mhs['nama'] ?? $data->user->name;
            $nimMhs = $mhs['nim'] ?? '-';
            $tingkat = $mhs['tingkat'] ?? '...';
            $semester = $mhs['semester'] ?? '...';
            $tempatLahir = $mhs['tempat_lahir'] ?? '...';
            $tglLahir = isset($mhs['tanggal_lahir']) ? \Carbon\Carbon::parse($mhs['tanggal_lahir'])->isoFormat('D MMMM Y') : '...';
            $alamat = $mhs['alamat'] ?? '...';
            
            // Logika Tahun Akademik (Otomatis berdasarkan tahun saat ini / input)
            $tahunIni = date('Y');
            $tahunDepan = $tahunIni + 1;
        @endphp

        <table class="tabel-data">
            <tr>
                <td class="label-col font-bold" style="letter-spacing: 2px;">Nama</td>
                <td class="sep-col">:</td>
                <td class="uppercase font-bold">{{ $namaMhs }}</td>
            </tr>
            <tr>
                <td class="label-col font-bold">Tk / N i m</td>
                <td class="sep-col">:</td>
                <td class="uppercase">{{ $tingkat }} / {{ $nimMhs }}</td>
            </tr>
            <tr>
                <td class="label-col font-bold">Tempat / Tgl lahir</td>
                <td class="sep-col">:</td>
                <td class="uppercase">{{ $tempatLahir }}, {{ strtoupper($tglLahir) }}</td>
            </tr>
            <tr>
                <td class="label-col font-bold">Alamat</td>
                <td class="sep-col">:</td>
                <td class="uppercase" style="vertical-align: top;">{{ $alamat }}</td>
            </tr>
        </table>

        {{-- ISI: PERNYATAAN --}}
        <div class="paragraph" style="margin-top: 20px;">
            Yang tersebut di atas adalah benar Mahasiswa STIKES Bhayangkara Makassar, 
            Tingkat {{ $tingkat }} Semester {{ $semester }} Tahun Akademik {{ $tahunIni }} / {{ $tahunDepan }}. 
            Yang tidak menerima Beasiswa dari Institusi dan Lembaga-lembaga lainnya
        </div>

        <div class="paragraph">
            Demikian surat keterangan ini dibuat dengan sebenarnya untuk digunakan sebagaimana mestinya.
        </div>

    {{-- TEMPLATE DEFAULT (NON-SKAK) --}}
    @else
        {{-- (Kode template default Anda sebelumnya di sini, tidak saya ubah agar fokus ke request) --}}
        <div class="text-center" style="margin-top: 50px;">
            <h3>{{ $data->type->name }}</h3>
            <p>Template belum dikonfigurasi secara spesifik.</p>
        </div>
    @endif

    {{-- BAGIAN TANDA TANGAN (SAMA PERSIS POSISINYA) --}}
    <div class="ttd-wrapper">
        <div class="ttd-tanggal">
            Makassar, &nbsp; {{ \Carbon\Carbon::parse($data->letter_date)->isoFormat('MMMM Y') }}
        </div>
        <div class="ttd-jabatan">KETUA,</div>

        {{-- LOGIKA QR CODE / TANDA TANGAN --}}
        @if(in_array($data->status, ['approved', 'completed']) && $data->signature_code)
            <div class="qr-box" style="margin-top: -50px; margin-bottom: 10px;">
                <img class="qr-img"
                     src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(100)->margin(1)->errorCorrection('H')->generate(route('letter.verify', $data->signature_code))) !!}"
                     alt="QR Code">
                {{-- Overlay Logo Kecil di Tengah QR --}}
                <img class="qr-overlay" src="{{ public_path('images/logo.png') }}" alt="Logo">
            </div>
        @else
            <div style="height: 60px; margin-top: -50px;"></div> {{-- Spacer kosong jika belum TTD --}}
        @endif

        <div class="ttd-nama">{{ $data->signer->user->name }}</div>
        <div class="font-bold">NUPTK : {{ $data->signer->employee_id ?? '....................' }}</div>
    </div>

    <div class="clear"></div>

</body>
</html>
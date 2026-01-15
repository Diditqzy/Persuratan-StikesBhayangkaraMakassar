<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat - {{ $data->letter_number }}</title>
    <style>
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
        
        /* KOP SURAT */
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

        /* KONTEN SURAT */
        .judul-surat {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12pt;
            text-decoration: underline;
        }
        .nomor-surat {
            text-align: center;
            font-size: 12pt;
            margin-top: 2px;
            margin-bottom: 25px;
        }
        .tabel-data {
            width: 100%;
            margin-left: 20px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .tabel-data td {
            vertical-align: top;
            padding: 2px 0;
        }
        .label-col { width: 140px; }
        .sep-col { width: 20px; text-align: center; }
        .paragraph {
            text-align: justify;
            text-indent: 40px;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .intro-text { margin-bottom: 10px; }

        /* TANDA TANGAN */
        .ttd-container {
            float: right;
            width: 280px;
            margin-top: 20px;
            text-align: center;
        }
        
/* WRAPPER QR CODE */
    .qr-wrapper {
        position: relative;    
        display: inline-block; 
        width: 100px;     
        height: 100px;           
        margin: 0 auto;  
    }

    /* GAMBAR QR  */
    .qr-image {
        width: 100%;
        height: 100%;
    }

    /* LOGO DI TENGAH  */
    .qr-logo {
        position: absolute;     
        top: 50%;    
        left: 50%;   
        
        /* UKURAN LOGO */
        width: 24px;             
        height: 24px; 
        margin-top: -12px;       
        margin-left: -12px;      
        background-color: #fff; 
        border-radius: 50%;   
        padding: 2px;
        z-index: 10;
    }
        .ttd-spacer {
            height: 100px; 
        }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo-ybubm.jpg') }}" class="logo-kiri" alt="Logo Yayasan">
            </td>
            <td width="70%" align="center">
                <div class="kop-text-yayasan uppercase">YAYASAN BRATA UTAMA BHAYANGKARA MAKASSAR</div>
                <div class="kop-text-kampus uppercase">SEKOLAH TINGGI ILMU KESEHATAN BHAYANGKARA MAKASSAR</div>
                <div class="kop-text-singkatan uppercase">( STIKBHARA MAKASSAR )</div>
                <div class="kop-text-alamat">
                    Jl. Letjen Pol. Mappa Oudang No. 63 Makassar 90223 HP/WA. 085824855515<br>
                    Website : www.stikbhara.ac.id &nbsp; Email : stikbhara@gmail.com
                </div>
            </td>
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo.png') }}" class="logo-kanan" alt="Logo STIKES">
            </td>
        </tr>
    </table>

    {{-- 2. ISI SURAT --}}
    @if($data->type_id == 1)
        {{-- JUDUL --}}
        <div class="judul-surat">SURAT KETERANGAN KULIAH</div>
        <div class="nomor-surat">Nomor : &nbsp;&nbsp; {{ $data->letter_number ?? '...../SKT...../...../2025/STIKBHARA' }}</div>

        {{-- DATA PENANDATANGAN --}}
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

        {{-- DATA MAHASISWA --}}
        <div class="intro-text" style="margin-top: 15px;">
            Menerangkan bahwa &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
        </div>
        @php
            $mhs = $data->additional_data;
            $namaMhs = $mhs['nama'] ?? $data->user->name;
            $nimMhs = $mhs['nim'] ?? '-';
            $tingkat = $mhs['tingkat'] ?? '...';
            $semester = $mhs['semester'] ?? '...';
            $tempatLahir = $mhs['tempat_lahir'] ?? '...';
            $tglLahir = isset($mhs['tanggal_lahir']) ? \Carbon\Carbon::parse($mhs['tanggal_lahir'])->isoFormat('D MMMM Y') : '...';
            $alamat = $mhs['alamat'] ?? '...';
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

        {{-- PARAGRAF PENUTUP --}}
        <div class="paragraph" style="margin-top: 20px;">
            Yang tersebut di atas adalah benar Mahasiswa STIKES Bhayangkara Makassar, 
            Tingkat {{ $tingkat }} Semester {{ $semester }} Tahun Akademik {{ $tahunIni }} / {{ $tahunDepan }}. 
            Yang tidak menerima Beasiswa dari Institusi dan Lembaga-lembaga lainnya
        </div>

        <div class="paragraph">
            Demikian surat keterangan ini dibuat dengan sebenarnya untuk digunakan sebagaimana mestinya.
        </div>

    @else
        <div class="text-center" style="margin-top: 50px;">
            <h3>{{ $data->type->name }}</h3>
            <p>Isi surat belum dikonfigurasi.</p>
        </div>
    @endif

    {{-- 3. AREA TANDA TANGAN (Menggunakan TABLE agar Layout Stabil) --}}
    <table class="ttd-container">
        {{-- Baris Tanggal --}}
        <tr>
            <td>
                Makassar, {{ \Carbon\Carbon::parse($data->letter_date)->isoFormat('D MMMM Y') }}
            </td>
        </tr>
        {{-- Baris Jabatan --}}
        <tr>
            <td class="font-bold" style="padding-bottom: 24px">
                KETUA,
            </td>
        </tr>
        {{-- Baris QR Code / Spacer --}}
 <tr>
    {{-- Align center di <td> sangat penting untuk PDF --}}
    <td align="center" style="vertical-align: middle;">
        
        @if(in_array($data->status, ['approved', 'completed']) && $data->signature_code)
            <div class="qr-wrapper">
                {{-- Gambar QR --}}
                <img class="qr-image" 
                     src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(100)->margin(0)->errorCorrection('H')->generate(route('letter.verify', $data->signature_code))) !!}" 
                     alt="QR">
                
                {{-- Gambar Logo Overlay --}}
                {{-- Class "qr-logo" sudah diatur posisinya di CSS --}}
                <img class="qr-logo" src="{{ public_path('images/logo.png') }}" alt="Logo">
            </div>
        @else
            {{-- Spacer jika belum TTD --}}
            <div class="ttd-spacer" style="height: 100px;"></div>
        @endif

    </td>
</tr>
        {{-- Baris Nama --}}
        <tr>
            <td class="font-bold underline">
                {{ $data->signer->user->name }}
            </td>
        </tr>
        {{-- Baris NUPTK --}}
        <tr>
            <td class="font-bold">
                NUPTK : {{ $data->signer->employee_id ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
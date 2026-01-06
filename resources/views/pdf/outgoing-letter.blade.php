<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat - {{ $data->letter_number }}</title>
    <style>
        /* PENGATURAN KERTAS A4 PORTRAIT */
        @page { margin: 2.5cm 2.5cm 3cm 2.5cm; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        /* KOP SURAT */
        .kop-surat {
            width: 100%;
            border-bottom: 4px double #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .kop-surat td { vertical-align: top; }
        .logo { width: 90px; height: auto; }
        .instansi-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 1px;
        }
        .instansi-address {
            font-size: 10pt;
            text-align: center;
            font-style: italic;
        }
        
        /* LOGIKA TAMPILAN BERDASARKAN JENIS SURAT */
        
        /* GAYA KHUSUS SURAT KETERANGAN (ID 1) */
        .judul-surat {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14pt;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .nomor-surat {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 30px;
        }
        .body-text {
            text-align: justify;
            margin-bottom: 15px;
        }
        .biodata-table {
            width: 100%;
            margin-left: 30px;
            margin-bottom: 15px;
        }
        .biodata-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* GAYA UMUM (MANUAL) */
        .meta-table { width: 100%; margin-bottom: 18px; }
        .meta-table td { vertical-align: top; }
        .content { margin-top: 15px; text-align: justify; min-height: 200px; }

        /* TANDA TANGAN */
        .ttd-container {
            margin-top: 40px;
            page-break-inside: avoid;
            float: right;
            width: 45%;
            text-align: center;
        }
        .qr-container {
            position: relative;
            width: 90px;
            height: 90px;
            margin: 10px auto;
        }
        .qr-code-image { width: 100%; height: 100%; }
        .qr-logo-overlay {
            position: absolute;
            top: 32px; left: 32px;
            width: 26px; height: 26px;
            background: #fff;
            padding: 2px;
        }
        .qr-note { font-size: 7pt; color: #555; margin-top: 2px; }
        .ttd-name { font-weight: bold; text-decoration: underline; margin-top: 10px; }
        .ttd-nip { font-size: 11pt; }
        .clear { clear: both; }
    </style>
</head>
<body>

    <table class="kop-surat">
        <tr>
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            </td>
            <td width="85%" align="center">
                <div class="instansi-name">SEKOLAH TINGGI ILMU KESEHATAN<br>(STIKES) BHAYANGKARA MAKASSAR</div>
                <div class="instansi-address">
                    Jl. Mappaodang No. 63, Makassar, Sulawesi Selatan<br>
                    Telp: (0411) 871234 | Email: info@stikes-bhayangkara.ac.id | Website: www.stikes-bhayangkara.ac.id
                </div>
            </td>
        </tr>
    </table>

    @if($data->type_id == 1)

        <div class="judul-surat">SURAT KETERANGAN AKTIF KULIAH</div>
        <div class="nomor-surat">Nomor: {{ $data->letter_number ?? '...../...../...../.....' }}</div>

        <div class="body-text">
            Yang bertanda tangan di bawah ini, Ketua STIKES Bhayangkara Makassar menerangkan bahwa:
        </div>

        <table class="biodata-table">
            <tr>
                <td width="30%">Nama Lengkap</td>
                <td width="2%">:</td>
                <td width="68%"><strong>{{ $data->user->name }}</strong></td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td>{{ $data->user->identity_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>S1 Keperawatan</td> 
            </tr>
            <tr>
                <td>Semester</td>
                <td>:</td>
                <td>Ganjil T.A {{ date('Y') }}/{{ date('Y')+1 }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>Makassar</td> 
            </tr>
        </table>

        <div class="body-text">
            Adalah benar mahasiswa yang bersangkutan terdaftar dan aktif mengikuti perkuliahan pada Semester Ganjil Tahun Akademik {{ date('Y') }}/{{ date('Y')+1 }} di STIKES Bhayangkara Makassar.
        </div>

        <div class="body-text">
            Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dipergunakan sebagaimana mestinya.
        </div>

    @else

        <table class="meta-table">
            <tr>
                <td width="12%">Nomor</td>
                <td width="2%">:</td>
                <td width="46%">{{ $data->letter_number ?? '___/STIKES/___/____' }}</td>
                <td width="40%" align="right">
                    Makassar, {{ \Carbon\Carbon::parse($data->letter_date)->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>{{ $data->attachments->count() ? $data->attachments->count().' Berkas' : '-' }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td style="font-weight: bold;">{{ $data->subject }}</td>
                <td></td>
            </tr>
        </table>

        <div style="margin-bottom: 15px;">
            Kepada Yth.<br>
            <strong>{{ $data->recipient }}</strong><br>
            di -<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempat
        </div>

        <div class="content">
            {!! $data->content_data !!}
        </div>

    @endif

    <div class="ttd-container">
        @if($data->type_id == 1)
            <div style="margin-bottom: 10px;">Makassar, {{ \Carbon\Carbon::parse($data->letter_date)->translatedFormat('d F Y') }}</div>
        @endif

        <div>{{ $data->signer->position }},</div>

        @if(in_array($data->status, ['approved', 'completed']) && $data->signature_code)
            <div class="qr-container">
                <img class="qr-code-image"
                     src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(100)->margin(1)->errorCorrection('H')->generate(route('letter.verify', $data->signature_code))) !!}"
                     alt="QR Code">
                <img class="qr-logo-overlay" src="{{ public_path('images/logo.png') }}" alt="Logo">
            </div>
            <div class="qr-note">Dokumen ini ditandatangani secara elektronik</div>
        @else
            <div style="height:80px; display:flex; align-items:center; justify-content:center; color:#ccc; border:1px dashed #ccc; margin:10px 0;">
                (Draft / Belum Final)
            </div>
        @endif

        <div class="ttd-name">{{ $data->signer->user->name }}</div>
        <div class="ttd-nip">NIP/NIK. {{ $data->signer->employee_id ?? '-' }}</div>
    </div>

    <div class="clear"></div>

</body>
</html>
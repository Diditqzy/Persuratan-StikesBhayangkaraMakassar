<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat - {{ $data->letter_number }}</title>
    <style>
        /* =======================
           PENGATURAN KERTAS
        ======================== */
        @page { margin: 2.5cm; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* =======================
           KOP SURAT
        ======================== */
        .kop-surat {
            width: 100%;
            border-bottom: 5px double #000;
            padding-bottom: 6px;
            margin-bottom: 20px;
        }
        .kop-surat td {
            vertical-align: middle;
        }
        .logo {
            width: 90px;
        }
        .instansi-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .instansi-address {
            font-size: 11pt;
            font-style: italic;
        }

        /* =======================
           META SURAT
        ======================== */
        .meta-table {
            width: 100%;
            margin-bottom: 18px;
        }
        .meta-table td {
            vertical-align: top;
        }

        /* =======================
           KONTEN
        ======================== */
        .content {
            margin-top: 15px;
            text-align: justify;
            min-height: 220px;
        }

        /* =======================
           TANDA TANGAN
        ======================== */
        .ttd-container {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .ttd-box {
            float: right;
            width: 45%;
            text-align: center;
        }

        /* =======================
           QR CODE + LOGO (FIX SIMETRIS)
        ======================== */
        .qr-container {
            position: relative;
            width: 100px; /* Lebar Wadah FIX 100px */
            height: 100px;
            margin: 12px auto 6px;
        }

        .qr-code-image {
            width: 100%;
            height: 100%;
            display: block;
            border: 0;
        }

        .qr-logo-overlay {
            position: absolute;
            
            /* --- PERBAIKAN MATEMATIKA --- */
            /* Lebar Gambar: 22px */
            /* Padding Kiri+Kanan: 4px */
            /* Total Lebar Objek: 26px */
            /* Wadah: 100px */
            /* Sisa Ruang: 100 - 26 = 74px */
            /* Bagi dua: 37px */
            
            top: 37px;
            left: 37px;

            width: 22px; 
            height: 22px;

            background: #fff;
            padding: 2px;
            
            /* Hapus border-radius biar kotak tegas */
            border-radius: 0; 
        }
        /* ======================= */

        .qr-note {
            font-size: 8pt;
            color: #555;
            margin-top: 4px;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
        }

        .ttd-nip {
            font-size: 11pt;
        }

        .bold { font-weight: bold; }
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
            <div class="instansi-name">STIKES BHAYANGKARA MAKASSAR</div>
            <div class="instansi-address">
                Jl. Mappaodang No. 63, Makassar, Sulawesi Selatan<br>
                Telp: (0411) 123456 | Email: info@stikes-bhayangkara.ac.id
            </div>
        </td>
    </tr>
</table>

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
        <td class="bold">{{ $data->subject }}</td>
        <td></td>
    </tr>
</table>

<div style="margin-bottom: 10px;">
    Kepada Yth.<br>
    <span class="bold">{{ $data->recipient }}</span><br>
    di -<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempat
</div>

<div class="content">
    {!! $data->content_data !!}
</div>

<div class="ttd-container">
    <div class="ttd-box">
        <div>{{ $data->signer->position }},</div>

        @if(in_array($data->status, ['approved', 'completed']) && $data->signature_code)

            <div class="qr-container">
                <img
                    class="qr-code-image"
                    src="data:image/svg+xml;base64,{!!
                        base64_encode(
                            QrCode::format('svg')
                                ->size(100)
                                ->margin(1)
                                ->errorCorrection('H')
                                ->generate(route('letter.verify', $data->signature_code))
                        )
                    !!}"
                    alt="QR Code"
                >

                <img
                    class="qr-logo-overlay"
                    src="{{ public_path('images/logo.png') }}"
                    alt="Logo"
                >
            </div>

            <div class="qr-note">
                Dokumen ini ditandatangani secara elektronik
            </div>

        @else

            <div style="height:100px;display:flex;align-items:center;justify-content:center;
                        color:#ccc;border:1px dashed #ccc;margin:10px 0;">
                (Draft / Belum Final)
            </div>

        @endif

        <div class="ttd-name">{{ $data->signer->user->name }}</div>
        <div class="ttd-nip">NIP/NIK. {{ $data->signer->employee_id ?? '-' }}</div>
    </div>
    <div class="clear"></div>
</div>

</body>
</html>
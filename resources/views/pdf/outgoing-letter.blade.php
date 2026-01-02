<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat - {{ $data->letter_number }}</title>
    <style>
        /* Pengaturan Kertas dan Margin */
        @page { margin: 2.5cm 2.5cm 2.5cm 2.5cm; }
        
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 12pt; 
            line-height: 1.5; 
            color: #000;
        }

        /* Kop Surat */
        .kop-surat { width: 100%; border-bottom: 5px double black; padding-bottom: 5px; margin-bottom: 20px; }
        .kop-surat td { vertical-align: middle; }
        .logo { width: 90px; height: auto; }
        .instansi-name { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .instansi-address { font-size: 11pt; font-style: italic; }

        /* Meta Data (Nomor, Lampiran, Perihal) */
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { vertical-align: top; }
        
        /* Konten Surat */
        .content { margin-top: 20px; text-align: justify; min-height: 200px; }
        
        /* Area Tanda Tangan */
        .ttd-container { 
            width: 100%; 
            margin-top: 40px; 
            page-break-inside: avoid; /* Jangan terpotong ke halaman baru */
        }
        .ttd-box { 
            float: right; 
            width: 45%; 
            text-align: center; 
        }
        .qrcode { margin: 10px auto; }
        .ttd-name { font-weight: bold; text-decoration: underline; margin-top: 10px; }
        .ttd-nip { font-size: 11pt; }

        /* Utility */
        .bold { font-weight: bold; }
        .clear { clear: both; }
    </style>
</head>
<body>

    <table class="kop-surat">
        <tr>
            <td width="15%" align="center">
                <img src="https://via.placeholder.com/100x100.png?text=LOGO" class="logo" alt="Logo">
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
            <td width="40%" align="right">Makassar, {{ \Carbon\Carbon::parse($data->letter_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>{{ $data->attachments->count() > 0 ? $data->attachments->count() . ' Berkas' : '-' }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td class="bold">{{ $data->subject }}</td>
            <td></td>
        </tr>
    </table>

    <div style="margin-top: 10px; margin-bottom: 10px;">
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

            <div class="qrcode">
                @if(in_array($data->status, ['approved', 'completed']) && $data->signature_code)
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate(route('letter.verify', $data->signature_code))) !!} ">
                    <br>
                    <span style="font-size: 8pt; color: #555;">Dokumen ini ditandatangani secara elektronik</span>
                @else
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center; color: #ccc; border: 1px dashed #ccc;">
                        (Draft / Belum Final)
                    </div>
                @endif
            </div>

            <div class="ttd-name">{{ $data->signer->user->name }}</div>
            <div class="ttd-nip">NIP/NIK. {{ $data->signer->employee_id ?? '-' }}</div>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
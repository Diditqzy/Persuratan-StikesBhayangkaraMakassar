<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; }
        .header { text-align: center; border-bottom: 3px double black; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 80px; height: auto; position: absolute; left: 0; top: 0; }
        .instansi { font-size: 14pt; font-weight: bold; }
        .alamat { font-size: 10pt; font-style: italic; }
        .content { margin-top: 20px; }
        .ttd-container { float: right; width: 40%; text-align: center; margin-top: 50px; }
        .ttd-name { font-weight: bold; text-decoration: underline; margin-top: 60px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td { vertical-align: top; }
        .label-col { width: 100px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="instansi">STIKES BHAYANGKARA MAKASSAR</div>
        <div class="alamat">Jl. Contoh Alamat No. 123, Makassar, Sulawesi Selatan</div>
    </div>

    <table>
        <tr>
            <td class="label-col">Nomor</td>
            <td>: {{ $data->letter_number ?? 'Belum Ada Nomor' }}</td>
            <td align="right">{{ $data->letter_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>: {{ $data->attachments->count() }} Berkas</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>: <b>{{ $data->subject }}</b></td>
        </tr>
    </table>

    <br>

    <div>
        Kepada Yth.<br>
        <b>{{ $data->recipient }}</b><br>
        di Tempat
    </div>

    <div class="content">
        {!! $data->content_data !!}
    </div>

    <div class="ttd-container">
        Makassar, {{ $data->letter_date->format('d F Y') }}<br>
        {{ $data->signer->position }}
        
        <br><br><br><br> <div class="ttd-name">{{ $data->signer->user->name }}</div>
        <div>NIP. {{ $data->signer->employee_id }}</div>
    </div>

</body>
</html>
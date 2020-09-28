<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Request Alasan</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	</head>
	<body style="margin: 0; padding: 0;width: 100%;height: 100%;background: #c6c6c6;">
		<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc;">
			<tr>
				<td bgcolor="#70bbd9" align="center" style="padding: 40px 0 30px 0;">
					<img src="aic.png" alt="AIC" width="300" height="200" style="display: block;" />
				</td>
			</tr>
			<tr>
				<td bgcolor="#ffffff" style="padding: 30px 30px 30px 30px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td style="color: #153643; font-family: Arial, sans-serif; font-size: 14px;">
								Yth, Pimpinan HRD IJ
							</td>
						</tr>
						<tr style="color: #153643; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px;">
							<td style="padding: 20px 0 30px 0;">
                                                            Kami mengirimkan permohonan alasan karyawan dengan no dokumen <b>{{$no}}</b> pada tanggal <b>{{$tanggal}}</b>, jam <b>{{$jam}}</b> untuk ditinjau permohonan ini.
<br/>
E-mail ini dibuat secara otomatis oleh aplikasi absensi PT. Indah Jaya Textile Industry untuk PT. APAC Inti Corpora.
							</td>
						</tr>
						<tr>
							<td align="center">
								<a href="{{$url}}" style="font-family: Arial, sans-serif; font-size: 14px;background-color:#0088cc; color: white; padding: 1em 1.5em; text-decoration: none;border-top: 1px solid #CCCCCC;border-right: 1px solid #333333;border-bottom: 1px solid #333333;border-left: 1px solid #CCCCCC;">Lihat Permohonan</a>
							</td>
						</tr>
						<tr style="color: #153643; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px;">
							<td style="padding: 10px 0 10px 0;">Hormat Kami,<br/>
{{ Auth::user()->name }}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td bgcolor="#ee4c50" style="padding: 30px 30px 30px 30px;color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>
							PT. APAC Inti Corpora
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>


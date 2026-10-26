@extends('email.common')

@section('content')
    <tr>
        <td style="padding: 20px;">
            <h2 style="margin: 0 0 15px; font-size: 18px; color: #333;">Meeting Notification</h2>
            <p style="margin: 0 0 10px; font-size: 14px; color: #666;">Halo {{ $attendee->name }},</p>
            <p style="margin: 0 0 10px; font-size: 14px; color: #666;">Anda diundang untuk mengikuti meeting berikut:</p>

            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; width: 150px;">Judul</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $meeting->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Tipe</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $meeting->meeting_type }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Tanggal</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $meeting->meeting_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Jam</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($meeting->meeting_time)->format('H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Deadline Pengisian</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $meeting->deadline->format('d M Y H:i') }}</td>
                </tr>
            </table>

            <p style="margin: 15px 0; font-size: 14px; color: #666;">
                Silakan isi hasil/kesimpulan meeting sebelum deadline.
            </p>

            <p style="margin: 0; font-size: 12px; color: #999;">
                Email ini dikirim otomatis oleh sistem HRIS.
            </p>
        </td>
    </tr>
@endsection

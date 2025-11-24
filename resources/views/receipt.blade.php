<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .receipt-title { font-size: 18px; margin-top: 5px; color: #555; }
        .details-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .label { font-weight: bold; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #777; }
        .status-paid { color: green; font-weight: bold; border: 1px solid green; padding: 5px 10px; display: inline-block; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">Exam Portal System</div>
        <div class="receipt-title">Examination Fee Receipt</div>
    </div>

    <div style="text-align: right; margin-bottom: 20px;">
        <span class="status-paid">PAID SUCCESSFUL</span>
    </div>

    <div class="details-box">
        <p><span class="label">Receipt No:</span> #{{ $payment->payment_id }}</p>
        <p><span class="label">Date:</span> {{ $payment->created_at->format('d-m-Y h:i A') }}</p>
        <p><span class="label">Student Name:</span> {{ $payment->user->name }}</p>
        <p><span class="label">Email:</span> {{ $payment->user->email }}</p>
    </div>

    <table width="100%" border="1" cellspacing="0" cellpadding="10" style="border-collapse: collapse;">
        <thead style="background-color: #f2f2f2;">
            <tr>
                <th>Exam Name</th>
                <th>Fee Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $submission->form->title }}</td>
                <td>INR {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td style="text-align: right; font-weight: bold;">Total Paid</td>
                <td style="font-weight: bold;">INR {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="footer">
        <p>Transaction ID: {{ $payment->payment_id }}</p>
    </div>

</body>
</html>
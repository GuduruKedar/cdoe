@extends('layouts.app')

@section('page_title', 'My Dashboard')

@section('content')

    <div class="stat-grid">
        <div class="stat-card green">
            <div class="label">Fee Status</div>
            <div class="value">PAID</div>
            <div class="note">Semester I</div>
        </div>

        <div class="stat-card blue">
            <div class="label">Result</div>
            <div class="value">PUBLISHED</div>
            <div class="note">Semester I</div>
        </div>

        <div class="stat-card yellow">
            <div class="label">SGPA</div>
            <div class="value">9.40</div>
            <div class="note">Current semester</div>
        </div>

        <div class="stat-card red">
            <div class="label">Open Tickets</div>
            <div class="value">0</div>
            <div class="note">Support requests</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap: 20px; margin-top: 22px;">

        <div class="card">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="#" class="btn-navy">View Results</a>
                <a href="#" class="btn-outline">Fee Payments</a>
                <a href="#" class="btn-red">Raise Ticket</a>
                <a href="#" class="btn-outline">Certificates</a>
            </div>
        </div>

        <div class="card">
            <h3>Latest Result</h3>
            <table style="width:100%; font-size:14px; color:#334155;">
                <tr><td>SGPA</td><td align="right"><b>9.40</b></td></tr>
                <tr><td>Total</td><td align="right"><b>437/500</b></td></tr>
                <tr><td>Division</td><td align="right"><b>FIRST CLASS WITH DISTINCTION</b></td></tr>
            </table>
        </div>

    </div>

@endsection

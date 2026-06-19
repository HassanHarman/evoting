<?php
// results/2025/index.php - Past Election Results
define('ACCESS_ALLOWED', true);
require_once '../../config/database.php';

// For demo purposes, we'll create static-looking data for 2025 elections
// In production, you'd have a proper archive table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2025 Election Results Archive - I.C.U.C University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
        }
        .archive-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .result-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .winner-name {
            color: #2c3e50;
            font-weight: bold;
        }
        .past-badge {
            background: #95a5a6;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
        }
        .btn-current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="archive-header">
        <div class="container">
            <h1><i class="fas fa-archive"></i> Election Results Archive</h1>
            <h2>2025 Student Union Elections</h2>
            <p class="lead">Official Results - Published March 15, 2025</p>
            <a href="../../index.php" class="btn btn-light mt-3">
                <i class="fas fa-arrow-left"></i> Back to Current Election
            </a>
            <a href="../public.php" class="btn btn-outline-light mt-3 ms-2">
                <i class="fas fa-chart-line"></i> View 2026 Results
            </a>
        </div>
    </div>
    
    <div class="container mt-5">
        <!-- Results Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="result-card text-center">
                    <h3>2,847</h3>
                    <p>Total Votes Cast</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="result-card text-center">
                    <h3>68.5%</h3>
                    <p>Voter Turnout</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="result-card text-center">
                    <h3>5</h3>
                    <p>Positions Contested</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="result-card text-center">
                    <h3>12</h3>
                    <p>Candidates</p>
                </div>
            </div>
        </div>
        
        <!-- President Results -->
        <div class="result-card">
            <h3><i class="fas fa-crown text-warning"></i> President</h3>
            <div class="alert alert-info">
                <strong>Winner:</strong> Sarah Mukasa
                <span class="past-badge ms-2">Former President 2025-2026</span>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Candidate</th><th>Votes</th><th>Percentage</th></tr>
                </thead>
                <tbody>
                    <tr class="table-success">
                        <td><strong>Sarah Mukasa</strong></td>
                        <td><strong>1,245</strong></td>
                        <td><strong>43.7%</strong></td>
                    </tr>
                    <tr>
                        <td>John Okello</td>
                        <td>987</td>
                        <td>34.7%</td>
                    </tr>
                    <
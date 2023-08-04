<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 18:21:51
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-16 09:24:47
 */
?>
<section class="nav-section">
    <div class="container-fluid">
        <div class="navbar">
            <nav class="navbar navbar-expand-lg navbar-light bg-light w-100">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">SWSM</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{url('dashboard')}}">Home</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="{{url('logout')}}">Logout</a>
                        </li>
                    </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</section>

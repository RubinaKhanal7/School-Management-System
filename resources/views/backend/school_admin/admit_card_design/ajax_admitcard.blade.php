<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f9f9f9;
    }

    .modal-content {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 86%;
        margin: 0 auto;
    }

    .modal-header {
        background-color: #3e8e41;
        color: #fff;
        font-size: 22px;
        padding: 15px 30px;
        border-radius: 12px 12px 0 0;
        text-align: center;
    }

    .modal-body {
        padding: 20px;
        border: 1px solid black;
    }

    .modal-title {
        font-size: 22px;
        font-weight: bold;
        color: #333;
    }

    .mark-container {
        background-color: #f2f2f2;
        background-size: cover;
        background-position: center;
        padding: 30px;
        border-radius: 12px;
        opacity: 0.9;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid #3C4D5E;
    }

    .info-text {
        color: black;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .info-text span {
        font-weight: bold;
        color: #333;
        width: 100%;
    }

    .stdbd {
        height: 100px;
        width: 100px;
        border: 2px solid black;
    }

    .sdphoto {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .ad_name {
        border: 1px solid #3C4D5E;
        border-radius: 16px;
        color: #3C4D5E;
        text-transform: uppercase;
    }

     /* Print-specific styles */
     @media print {
        body * {
            visibility: hidden;
        }
        
        .modal {
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
            min-height: 100%;
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .modal-content {
            border: none !important;
            box-shadow: none !important;
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .modal * {
            visibility: visible;
        }

        .modal-header {
            display: none !important;
        }

        .btn-close, 
        .btn-primary {
            display: none !important;
        }

        .modal-body {
            padding: 0;
            margin: 0;
            border: none !important;
        }

        .mark-container {
            margin: 0;
            padding: 20px;
            box-shadow: none !important;
        }

        /* Preserve image dimensions */
        .modal-logo,
        .sdphoto {
            max-width: 500px !important;
            max-height: 500px !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .modal-header,
        .mark-container {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .info-text,
        .modal-title,
        .ad_name {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg landscape-modal" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h4 class="modal-title">View Admit Card</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="printAdmitCard()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" id="certificate_detail">
                <div class="mark-container">
                    <div class="main-table">
                        <div class="d-flex row p-4">
                            <img src="data:image/png;base64,{{ $base64EncodedImageLeft }}" width="120" height="120"
                                class="modal-logo col-md-3">
                            <div class="d-flex flex-column col-md-8 align-items-center">
                                <p class="modal-title">{{ $admitCard->school->name ?? 'School Name Not Available' }}</p>
                                <h4 class="py-1" style="font-size:16px; text-transform: uppercase;">
                                    {{ $examination->exam }}
                                    <span id="isSession" style="padding-left:8px; color:black;">{{ $examination->academicSession->session ?? 'N/A' }}</span>
                                </h4>
                                <span class="ad_name rounde p-2 px-4" style="font-size:16px;">admit card</span>
                            </div>
                        </div>
                        <div class="row d-flex gap-3">
                            <div class="col-8">
                                <div>
                                    @if ($admitCard->is_name == 1 && !empty($student->user->f_name))
                                        <p class="info-text">Name Of Student : <span>{{ $student->user->f_name }}</span></p>
                                    @endif
                                </div>
                                {{-- <div>
                                    @if ($admitCard->is_name == 1 && !empty($student->user->f_name))
                                        <p class="info-text">Father name : <span>{{ $student->user->father_name }}</span></p>
                                    @endif
                                </div> --}}
                                <div class="d-flex justify-content-between">
                                    <div>
                                        @if ($admitCard->is_class == 1 && !empty($student->class_id))
                                            <p class="info-text">Class: <span>{{ $student->class->class ?? '' }}
                                                    ({{ $student->section->section_name ?? '' }})</span></p>
                                        @endif
                                    </div>
                                    <div>
                                        @if ($admitCard->is_roll_no == 1 && !empty($student->roll_no))
                                            <p class="info-text">Roll No: <span>{{ $student->roll_no }}</span></p>
                                        @endif
                                    </div>
                                    <div class="">
                                        @if ($admitCard->is_gender == 1 && !empty($student->user->gender))
                                            <p class="info-text">Gender: <span>{{ $student->user->gender }}</span></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="stdbd col-3 d-flex align-items-center justify-content-center">
                                @if($student->user->student_photo)
                                    <img src="data:image/png;base64,{{ $student->user->student_photo }}"
                                         class="sdphoto col-md-11 p-1"
                                         alt="Student Photo">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="sdphoto col-md-11 p-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function printAdmitCard() {
        const scrollPos = window.scrollY;
        document.body.style.overflow = 'hidden';

        document.querySelector('.modal').style.display = 'block';

        Promise.all(Array.from(document.images).filter(img => !img.complete).map(img => new Promise(resolve => {
            img.onload = img.onerror = resolve;
        }))).then(() => {
            window.print();
            setTimeout(() => {
                document.body.style.overflow = '';
                window.scrollTo(0, scrollPos);
            }, 100);
        });
    }
    </script>
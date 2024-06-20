<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouton de Like</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        } */

        .like-container {
            display: flex;
            align-items: center;
        }

        .like-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 2rem;
            transition: transform 0.2s ease;
        }

        .like-button:focus {
            outline: none;
        }

        .thumb-icon {
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .like-button:hover .thumb-icon {
            color: #888;
            transform: scale(1.1);
        }

        .liked .thumb-icon {
            color: #007BFF;
            transform: scale(1.2);
        }

        .like-count {
            margin-left: 10px;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .liked + .like-count {
            color: #007BFF;
        }
    </style>
</head>
<body>
    <div class="like-container">
        <button id="likeButton" class="like-button">
            <span id="thumbIcon" class="thumb-icon"><i class="far fa-thumbs-up"></i></span>
        </button>
        <span id="likeCount" class="like-count">0</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const likeButton = document.getElementById('likeButton');
            const likeCount = document.getElementById('likeCount');
            const thumbIcon = document.querySelector('.thumb-icon i');
            let isLiked = false;
            let count = 0;

            likeButton.addEventListener('click', () => {
                if (isLiked) {
                    count--;
                    likeButton.classList.remove('liked');
                    thumbIcon.classList.remove('fas');
                    thumbIcon.classList.add('far');
                } else {
                    count++;
                    likeButton.classList.add('liked');
                    thumbIcon.classList.remove('far');
                    thumbIcon.classList.add('fas');
                }
                isLiked = !isLiked;
                likeCount.textContent = count;
            });
        });
    </script>
</body>
</html>

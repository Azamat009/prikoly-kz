import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', () => {
    const videoFeed = document.getElementById('video-feed');
    const loadingIndicator = document.querySelector('.loading');
    let currentPage = 1;
    let isLoading = false;
    let videosLoaded = false;

    function createVideoElement(video) {
        const container = document.createElement('div');
        container.className = 'video-container';

        const videoEl = document.createElement('video');
        videoEl.playsInline = true;
        videoEl.muted = true;
        videoEl.autoplay = true;
        videoEl.loop = true;
        videoEl.src = video.filePath;

        const infoDiv = document.createElement('div');
        infoDiv.className = 'video-info';
        infoDiv.innerHTML = `<h3 class="video-title">${video.title}</h3>`;

        container.appendChild(videoEl);
        container.appendChild(infoDiv);

        videoEl.addEventListener('click', () => {
            videoEl.muted = !videoEl.muted;
        });

        return container;
    }

    async function loadVideos() {
        if (isLoading) return;
        isLoading = true;

        try {
            const response = await fetch(`/api/videos?page=${currentPage}`);
            const data = await response.json();

            if (data.videos.length > 0) {
                data.videos.forEach(video => {
                    const videoElement = createVideoElement(video);
                    videoFeed.appendChild(videoElement);
                });

                videosLoaded = true;
                loadingIndicator.style.display = 'none';
                currentPage = data.nextPage || currentPage;

                if (currentPage === 1) {
                    const firstVideo = videoFeed.querySelector('video');
                    if (firstVideo) {
                        firstVideo.play().catch(e => console.log('Автовоспроизведение:', e));
                    }
                }
            }
        } catch (error) {
            console.error('Ошибка загрузки:', error);
        } finally {
            isLoading = false;
        }
    }

    function handleScroll() {
        if (isLoading || !videosLoaded) return;

        const lastVideo = videoFeed.lastElementChild;
        if (!lastVideo) return;

        const lastVideoOffset = lastVideo.offsetTop + lastVideo.clientHeight;
        const pageOffset = window.pageYOffset + window.innerHeight;

        if (pageOffset > lastVideoOffset - 10) {
            loadVideos();
        }
    }

    loadVideos();
    window.addEventListener('scroll', handleScroll);

    function handleTouchStart(event) {
        if (videosLoaded) {
            event.preventDefault();
        }
    }

    let touchStartY = 0;
    videoFeed.addEventListener('touchstart', e => {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    videoFeed.addEventListener('touchend', e => {
        const touchEndY = e.changedTouches[0].clientY;
        const diff = touchStartY - touchEndY;

        if (diff > 50) {
            loadVideos();
        }
    }, { passive: true });
});
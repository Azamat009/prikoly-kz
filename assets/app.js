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
    let page = 1;
    let isLoading = false;

    async function loadVideos() {
        if (isLoading) return;
        isLoading = true;

        try {
            const response = await fetch(`/api/videos?page=${page}`);
            const data = await response.json();

            data.videos.forEach(video => {
                videoFeed.appendChild(createVideoElement(video));
            });

            page = data.nextPage || page;
        } finally {
            isLoading = false;
        }
    }

    function createVideoElement(video){
        const element = document.createElement('div');
        element.className = 'video-item';
        element.innerHTML = `
            <video controls src="${video.filePath}"></video>
            <h3>${video.title}</h3>
            <p>${video.description}</p>
            <div class="reactions">
                ${['haha', 'like', 'love', 'sad'].map(emotion =>
                    `<button class="reaction-btn"
                            data-emotion="${emotion}"
                            data-video-id="${video.id}">
                        ${getEmoji(emotion)}</button>`).join('')}
            </div>`;
        return element;
    }

    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
            loadVideos();
        }
    });

    loadVideos();

    function getEmoji(emotion) {
        const emojis = {
            haha: '😆',
            like: '👍',
            love: '❤️',
            sad: '😢'
        };
        return emojis[emotion] || '';
    }
});
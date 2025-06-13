import {Controller} from "@hotwired/stimulus";
import axios from 'axios';

export default class extends Controller {
    static targets = ['videoFeed', 'loader'];
    static values = {
        apiUrl: String,
        nextPage: Number
    };

    isLoading = false;
    hasMore = true;
    observer = null;
    videos = [];
    currentPage = 1;

    connect() {
        console.log('Video controller initiated');
        this.setupObservers();
        this.loadVideos();
    }

    async loadVideos() {
        if (this.isLoading || !this.hasMore) return;
        if (this.nextPageValue === 0)
            this.nextPageValue=1;
        this.isLoading = true;
        this.showLoader();

        try {
            const url = new URL(this.apiUrlValue, window.location.origin);
            url.searchParams.append('page', this.nextPageValue);

            const response = await axios.get(url.toString());
            const newVideos = response.data.videos;

            this.renderVideos(newVideos);
            this.nextPageValue = response.data.nextPage || null;
            this.hasMore = this.nextPageValue !== null;

        } catch (error) {
            console.error('Error fetching videos:', error);
        } finally {
            this.isLoading = false;
            this.hideLoader();
        }
    }

    setupObservers() {
        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    const video = entry.target.querySelector('video');
                    if (!video) return;

                    if (entry.isIntersecting) {
                        video.play().catch(e => console.log('Play error:', e));
                    } else {
                        if (!video.paused) video.pause();
                    }
                });
            },
            {
                root: this.element,
                threshold: 0.8
            }
        );

        this.element.addEventListener('scroll', this.handleScroll.bind(this));
    }

    handleScroll() {
        const {scrollHeight, scrollTop, clientHeight} = this.element;
        const threshold = 100;

        if (scrollHeight - scrollTop - clientHeight < threshold && !this.isLoading) {
            this.loadVideos();
        }
    }

    renderVideos(videos) {
        const startIndex = this.videoFeedTarget.children.length;

        videos.forEach((video, i) => {
            const globalIndex = startIndex + i;
            const videoElement = this.createVideoElement(video, globalIndex);
            this.videoFeedTarget.appendChild(videoElement);

            this.observer.observe(videoElement);
        });
    }

    createVideoElement(video, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'video-container';
        wrapper.dataset.index = index;

        const title = document.createElement('h3');
        title.textContent = video.title;
        title.className = 'video-title';

        const videoTag = document.createElement('video');
        videoTag.playsInline = true;
        videoTag.muted = true;
        videoTag.controls = false;
        videoTag.dataset.videoId = video.id;

        const source = document.createElement('source');
        source.src = video.filePath;
        source.type = 'video/mp4';

        const soundIcon = document.createElement('div');
        soundIcon.className = 'sound-icon';
        // soundIcon.dataset.action = 'click->video-feed#toogleSound'
        soundIcon.innerHTML = '🔇';
        soundIcon.style.position = 'absolute';
        soundIcon.style.bottom = '10px';
        soundIcon.style.right = '10px';
        soundIcon.style.zIndex = '10';
        soundIcon.style.backgroundColor = 'rgba(0,0,0,0.5)';
        soundIcon.style.borderRadius = '50%';
        soundIcon.style.width = '30px';
        soundIcon.style.height = '30px';
        soundIcon.style.display = 'flex';
        soundIcon.style.alignItems = 'center';
        soundIcon.style.justifyContent = 'center';
        soundIcon.style.color = 'white';
        soundIcon.style.cursor = 'pointer';

        videoTag.appendChild(source);
        wrapper.appendChild(title);
        wrapper.appendChild(videoTag);
        wrapper.appendChild(soundIcon);


        soundIcon.addEventListener('click', () => {
            if (videoTag.muted){
                soundIcon.innerHTML = '🔊';
                videoTag.muted = false;
            } else {
                soundIcon.innerHTML = '🔇';
                videoTag.muted = true;
            }
        });
        videoTag.addEventListener('click', () => {
            if (!videoTag.paused && videoTag.muted) {
                videoTag.muted = false;
                soundIcon.innerHTML = '🔊';
            } else if (!videoTag.paused && !videoTag.muted){
                videoTag.pause();
            } else {
                videoTag.play();
            }
        });

        return wrapper;
    }


    showLoader() {
        if (this.loaderTarget) this.loaderTarget.style.display = 'block';
    }

    hideLoader() {
        if (this.loaderTarget) this.loaderTarget.style.display = 'none';
    }

    disconnect() {
        this.observer?.disconnect();
        this.element.removeEventListener('scroll', this.handleScroll);
        super.disconnect();
    }
}
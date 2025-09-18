(function () {
    const selectionKey = "ecogoSelectedMedia";
    const likePrefix = "ecogoLikes:";
    const commentPrefix = "ecogoComments:";

    const container = document.getElementById("detailShell");

    function parseSelection() {
        try {
            const raw = localStorage.getItem(selectionKey);
            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            console.warn("Unable to read media selection", error);
            return null;
        }
    }

    function setActiveNav(page) {
        if (!page) {
            return;
        }
        const activeNav = document.querySelector(`nav a[data-nav='${page}']`);
        if (activeNav) {
            activeNav.classList.add("active");
        }
    }

    function createEmptyState() {
        const empty = document.createElement("div");
        empty.className = "detail-empty";
        empty.innerHTML = `
            <h1>Select a story to view</h1>
            <p>Choose a video or gallery from the energy or community pages to see the full details here.</p>
            <div>
                <a href="energyPage.html">Browse energy ideas</a>
                &nbsp;|&nbsp;
                <a href="communityPage.html">Explore community projects</a>
            </div>
        `;
        container.appendChild(empty);
    }

    function renderMediaElement(data) {
        const mediaWrapper = document.createElement("div");
        mediaWrapper.className = "detail-media";

        if (data.mediaType === "video") {
            const video = document.createElement("video");
            video.controls = true;
            video.playsInline = true;
            video.poster = data.poster || "";

            if (data.mediaSrc) {
                const source = document.createElement("source");
                source.src = data.mediaSrc;
                source.type = "video/mp4";
                video.appendChild(source);
            } else {
                const placeholder = document.createElement("div");
                placeholder.className = "detail-empty";
                placeholder.innerHTML = `
                    <h1>Video coming soon</h1>
                    <p>The contributor has not uploaded the video file yet. Check back later or contact the uploader.</p>
                `;
                mediaWrapper.appendChild(placeholder);
                return { wrapper: mediaWrapper, element: placeholder };
            }

            mediaWrapper.appendChild(video);
            return { wrapper: mediaWrapper, element: video };
        }

        const img = document.createElement("img");
        img.src = data.mediaSrc || data.poster || "";
        img.alt = data.alt || data.title || "Selected media";
        mediaWrapper.appendChild(img);
        return { wrapper: mediaWrapper, element: img };
    }

    function renderMetaSection(data) {
        const meta = document.createElement("div");
        meta.className = "detail-meta";

        if (data.category) {
            const tag = document.createElement("span");
            tag.className = "meta-tag";
            tag.textContent = data.category;
            meta.appendChild(tag);
        }

        if (data.duration) {
            const duration = document.createElement("span");
            duration.textContent = data.duration;
            meta.appendChild(duration);
        }

        if (data.uploader) {
            const uploader = document.createElement("span");
            uploader.className = "uploader";
            uploader.textContent = data.uploader;
            meta.appendChild(uploader);
        }

        if (data.tags && data.tags.length) {
            data.tags.forEach((tagValue) => {
                const tag = document.createElement("span");
                tag.className = "meta-tag";
                tag.textContent = tagValue;
                meta.appendChild(tag);
            });
        }

        return meta;
    }

    function setupLikeButton(actionsNode, data) {
        const likeKey = `${likePrefix}${data.title}`;
        const likedBefore = localStorage.getItem(likeKey) === "1";

        const button = document.createElement("button");
        button.type = "button";
        button.className = "like-button";
        button.textContent = likedBefore ? "You like this" : "Like this";
        if (likedBefore) {
            button.classList.add("liked");
        }

        button.addEventListener("click", () => {
            const isLiked = button.classList.toggle("liked");
            button.textContent = isLiked ? "You like this" : "Like this";
            localStorage.setItem(likeKey, isLiked ? "1" : "0");
        });

        actionsNode.appendChild(button);
        return button;
    }

    function setupFullscreenButton(actionsNode, mediaElement) {
        if (!mediaElement.requestFullscreen && !mediaElement.webkitRequestFullscreen) {
            return;
        }
        const button = document.createElement("button");
        button.type = "button";
        button.className = "fullscreen-button";
        button.textContent = "View full screen";
        button.addEventListener("click", () => {
            if (mediaElement.requestFullscreen) {
                mediaElement.requestFullscreen();
            } else if (mediaElement.webkitRequestFullscreen) {
                mediaElement.webkitRequestFullscreen();
            }
        });
        actionsNode.appendChild(button);
    }

    function loadComments(title) {
        try {
            const raw = localStorage.getItem(`${commentPrefix}${title}`);
            return raw ? JSON.parse(raw) : [];
        } catch (error) {
            console.warn("Unable to load comments", error);
            return [];
        }
    }

    function saveComments(title, comments) {
        localStorage.setItem(`${commentPrefix}${title}`, JSON.stringify(comments));
    }

    function renderCommentsSection(data) {
        const wrapper = document.createElement("section");
        wrapper.className = "detail-comments";

        const heading = document.createElement("h2");
        heading.textContent = "Comments";
        wrapper.appendChild(heading);

        const comments = loadComments(data.title);
        const list = document.createElement("ul");
        list.className = "comment-list";

        function appendComment(comment) {
            const item = document.createElement("li");
            const author = document.createElement("strong");
            author.textContent = comment.author;
            const time = document.createElement("span");
            time.textContent = new Date(comment.timestamp).toLocaleString();
            const body = document.createElement("p");
            body.textContent = comment.message;
            item.append(author, time, body);
            list.prepend(item);
        }

        comments.forEach(appendComment);

        const form = document.createElement("form");
        form.className = "comment-form";
        form.innerHTML = `
            <label for="commentMessage">Add a comment</label>
            <textarea id="commentMessage" name="commentMessage" placeholder="Share encouragement, tips, or questions" required></textarea>
            <button type="submit">Post comment</button>
        `;

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            const textarea = form.commentMessage;
            const message = textarea.value.trim();
            if (!message) {
                return;
            }
            const comment = {
                author: "You",
                message,
                timestamp: new Date().toISOString()
            };
            comments.push(comment);
            saveComments(data.title, comments);
            appendComment(comment);
            textarea.value = "";
        });

        wrapper.appendChild(form);
        wrapper.appendChild(list);
        return wrapper;
    }

    function renderDetail(data) {
        const shell = document.createElement("div");
        shell.className = "detail-shell";

        const { wrapper: mediaWrapper, element: mediaElement } = renderMediaElement(data);
        shell.appendChild(mediaWrapper);

        const body = document.createElement("div");
        body.className = "detail-body";

        const meta = renderMetaSection(data);
        if (meta.childNodes.length) {
            body.appendChild(meta);
        }

        const title = document.createElement("h1");
        title.textContent = data.title || "Community story";
        body.appendChild(title);

        if (data.description) {
            const description = document.createElement("p");
            description.textContent = data.description;
            body.appendChild(description);
        }

        const actions = document.createElement("div");
        actions.className = "detail-actions";
        const likeButton = setupLikeButton(actions, data);
        setupFullscreenButton(actions, mediaElement);

        const backButton = document.createElement("button");
        backButton.type = "button";
        backButton.className = "fullscreen-button";
        backButton.textContent = "Go back";
        backButton.addEventListener("click", () => {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = data.page === "community" ? "communityPage.html" : "energyPage.html";
            }
        });
        actions.appendChild(backButton);
        body.appendChild(actions);

        body.appendChild(renderCommentsSection(data));

        shell.appendChild(body);
        container.appendChild(shell);
    }

    const selection = parseSelection();
    if (!selection) {
        createEmptyState();
        return;
    }

    setActiveNav(selection.page);
    renderDetail(selection);
})();

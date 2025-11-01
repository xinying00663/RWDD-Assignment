(function () {
    const selectionKey = "ecogoSelectedMedia";

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
                <a href="energyPage.php">Browse energy ideas</a>
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
                source.type = data.mediaMime || "video/mp4";
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

    function createBackButton(data) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "detail-back-button";
        button.textContent = "Go back";
        button.addEventListener("click", () => {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = data.page === "community" ? "communityPage.html" : "energyPage.php";
            }
        });
        return button;
    }

    function renderDetail(data) {
        const shell = document.createElement("div");
        shell.className = "detail-shell";

        const nav = document.createElement("div");
        nav.className = "detail-nav";
        nav.appendChild(createBackButton(data));
        shell.appendChild(nav);

        const contentWrapper = document.createElement("div");
        contentWrapper.className = "detail-content-wrapper";

        const { wrapper: mediaWrapper, element: mediaElement } = renderMediaElement(data);
        contentWrapper.appendChild(mediaWrapper);

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

        contentWrapper.appendChild(body);
        shell.appendChild(contentWrapper);
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

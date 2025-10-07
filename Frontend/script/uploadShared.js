/**
 * Shared helpers for handling EcoGo upload flows backed by localStorage.
 */
(function () {
    const api = {
        readFileAsDataURL(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.addEventListener("load", () => resolve(reader.result));
                reader.addEventListener("error", () => reject(reader.error));
                reader.readAsDataURL(file);
            });
        },
        determineMediaType(file) {
            if (!file) {
                return "image";
            }
            const mime = (file.type || "").toLowerCase();
            if (mime.startsWith("video/")) {
                return "video";
            }
            if (mime.startsWith("image/")) {
                return "image";
            }
            const name = (file.name || "").toLowerCase();
            if (name.endsWith(".mp4") || name.endsWith(".mov") || name.endsWith(".avi") || name.endsWith(".webm")) {
                return "video";
            }
            return "image";
        },
        loadEntries(storageKey) {
            if (!storageKey) {
                return [];
            }
            try {
                const raw = localStorage.getItem(storageKey);
                if (!raw) {
                    return [];
                }
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                console.warn("EcoGo uploads: unable to parse entries", error);
                return [];
            }
        },
        saveEntries(storageKey, entries) {
            if (!storageKey) {
                return;
            }
            localStorage.setItem(storageKey, JSON.stringify(entries));
        },
        createEntryId(prefix) {
            const stamp = Date.now();
            const random = Math.random().toString(36).slice(2, 8);
            return `${prefix || "upload"}-${stamp}-${random}`;
        },
        setFlash(flashKey, payload) {
            if (!flashKey) {
                return;
            }
            localStorage.setItem(flashKey, JSON.stringify(payload || {}));
        },
        consumeFlash(flashKey) {
            if (!flashKey) {
                return null;
            }
            try {
                const raw = localStorage.getItem(flashKey);
                localStorage.removeItem(flashKey);
                return raw ? JSON.parse(raw) : null;
            } catch (error) {
                console.warn("EcoGo uploads: unable to parse flash payload", error);
                localStorage.removeItem(flashKey);
                return null;
            }
        },
        removeEntry(storageKey, id) {
            if (!storageKey || !id) {
                return [];
            }
            const entries = api.loadEntries(storageKey).filter((entry) => entry.id !== id);
            api.saveEntries(storageKey, entries);
            return entries;
        }
    };

    window.ecogoUploads = api;
})();

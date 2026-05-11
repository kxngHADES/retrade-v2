async function convertToAvif(file) {
	return new Promise((resolve, reject) => {
		if (!file) return resolve(null);

		const img = document.createElement("img");
		const canvas = document.createElement("canvas");
		const ctx = canvas.getContext("2d");

		img.onload = () => {
			canvas.width = img.width;
			canvas.height = img.height;
			ctx.drawImage(img, 0, 0);

			canvas.toBlob((blob) => {
				if (!blob) return reject("AVIF conversion failed");
				resolve(blob);
			}, "image/avif", 0.8);
		};

		img.onerror = () => reject("Image load failed");
		img.src = URL.createObjectURL(file);
	});
}

async function uploadToMinio(blob, path) {
	if (!blob) return null;

	const formData = new FormData();
	formData.append("file", blob);

	const res = await fetch(`/lib/services/upload.php?path=${encodeURIComponent(path)}`, {
		method: "POST",
		body: formData
	});

	const raw = await res.text();
	let data;
	try {
		data = JSON.parse(raw);
	} catch (e) {
		console.error("Non-JSON response from upload.php:", raw);
		throw new Error(`Server returned invalid response (HTTP ${res.status})`);
	}

	if (!res.ok) {
		throw new Error(data.details || data.error || `Upload failed (HTTP ${res.status})`);
	}
	return data.url;
}

const thumbnailInput = document.getElementById("thumbnail");
const imagesInput = document.getElementById("images");
const thumbnailPreview = document.querySelector(".listing-preview-img--thumbnail");
const imagePreviewSlots = Array.from(document.querySelectorAll(".listing-preview-img--small"));

function setPreviewImage(imgElement, file) {
	if (!imgElement) return;
	if (imgElement.dataset.previewUrl) {
		URL.revokeObjectURL(imgElement.dataset.previewUrl);
		delete imgElement.dataset.previewUrl;
	}

	if (!file) {
		imgElement.classList.remove("visible");
		const panel = imgElement.closest(".listing-upload-card");
		if (panel) {
			const content = panel.querySelector(".listing-upload-card-content");
			if (content) content.classList.remove("hidden");
		}
		return;
	}

	const previewUrl = URL.createObjectURL(file);
	imgElement.src = previewUrl;
	imgElement.dataset.previewUrl = previewUrl;
	imgElement.classList.add("visible");
	const panel = imgElement.closest(".listing-upload-card");
	if (panel) {
		const content = panel.querySelector(".listing-upload-card-content");
		if (content) content.classList.add("hidden");
	}
}

function onThumbnailChange() {
	const file = thumbnailInput.files[0];
	setPreviewImage(thumbnailPreview, file);
}

function onImagesChange() {
	const files = Array.from(imagesInput.files);

	imagePreviewSlots.forEach((imgEl, index) => {
		setPreviewImage(imgEl, files[index] || null);
	});
}

if (thumbnailInput) {
	thumbnailInput.addEventListener("change", onThumbnailChange);
}

if (imagesInput) {
	imagesInput.addEventListener("change", onImagesChange);
}

document.querySelector("form").addEventListener("submit", async (e) => {
	e.preventDefault();

	const form = e.target;
	const submitButton = form.querySelector(".listing-submit-btn");
	const previousButtonText = submitButton ? submitButton.textContent : null;

	if (submitButton) {
		submitButton.disabled = true;
		submitButton.classList.add("listing-submit-btn--loading");
		submitButton.setAttribute("aria-busy", "true");
	}

	try {
		const uid = window.UID;
		const name = form.name.value.trim();

		if (!name) {
			alert("Listing name is required");
			throw new Error("Missing listing name");
		}

		const tags = (form.tags?.value || "")
			.split(",")
			.map(t => t.trim())
			.filter(Boolean);

		const thumbnailFile = document.getElementById("thumbnail").files[0];
		const imageFiles = document.getElementById("images").files;

		if (!thumbnailFile) {
			alert("Thumbnail required");
			throw new Error("Thumbnail required");
		}

		// Upload thumbnail
		const thumbnailAvif = await convertToAvif(thumbnailFile);
		const thumbPath = `${uid}/${name}_thumbnail.avif`;
		const thumbnailUrl = await uploadToMinio(thumbnailAvif, thumbPath);

		// Upload images
		const imageUrls = [];
		for (let i = 0; i < imageFiles.length; i++) {
			const avif = await convertToAvif(imageFiles[i]);
			const path = `${uid}/${name}/${Date.now()}_${i}.avif`;
			const url = await uploadToMinio(avif, path);
			if (url) imageUrls.push(url);
		}

		// Populate hidden fields
		document.getElementById("thumbnail_url").value = thumbnailUrl || "";
		document.getElementById("list_of_image_url").value = JSON.stringify(imageUrls);

		let tagsInput = document.getElementById("tags_input");
		if (!tagsInput) {
			tagsInput = document.createElement("input");
			tagsInput.type = "hidden";
			tagsInput.name = "tags";
			tagsInput.id = "tags_input";
			form.appendChild(tagsInput);
		}
		tagsInput.value = JSON.stringify(tags);

		form.submit();

	} catch (err) {
		console.error("Upload failed:", err);
		alert("Upload failed: " + err.message);

		if (submitButton) {
			submitButton.disabled = false;
			submitButton.classList.remove("listing-submit-btn--loading");
			submitButton.removeAttribute("aria-busy");
		}
	}
});
document.addEventListener('DOMContentLoaded', function () {
  const firstNameInput = document.getElementById('firstName');
  const lastNameInput = document.getElementById('lastName');
  const saveInfoBtn = document.getElementById('save-info-btn');

  const phoneInput = document.getElementById('phoneNumber');
  const savePhoneBtn = document.getElementById('save-phone-btn');

  const emailInput = document.getElementById('email');
  const verifyEmailBtn = document.getElementById('verify-email-btn');
  const updateEmailBtn = document.getElementById('update-email-btn');

  const normalize = value => (value || '').trim();
  const normalizeEmail = value => (value || '').trim().toLowerCase();

  const isVerifiedEmail = verifyEmailBtn?.dataset?.verified === 'true';

  const setButtonState = (button, enabled) => {
    if (!button) return;
    button.disabled = !enabled;
  };

  const checkPersonalInfoState = () => {
    if (!firstNameInput || !lastNameInput || !saveInfoBtn) return;
    const firstChanged = normalize(firstNameInput.value) !== normalize(firstNameInput.dataset.original);
    const lastChanged = normalize(lastNameInput.value) !== normalize(lastNameInput.dataset.original);
    setButtonState(saveInfoBtn, firstChanged || lastChanged);
  };

  const checkPhoneState = () => {
    if (!phoneInput || !savePhoneBtn) return;
    const phoneChanged = normalize(phoneInput.value) !== normalize(phoneInput.dataset.original);
    setButtonState(savePhoneBtn, phoneChanged);
  };

  const checkEmailState = () => {
    if (!emailInput || !updateEmailBtn) return;
    const originalEmail = normalizeEmail(emailInput.dataset.original || '');
    const currentEmail = normalizeEmail(emailInput.value);
    const emailChanged = currentEmail.length > 0 && currentEmail !== originalEmail;

    setButtonState(updateEmailBtn, emailChanged);
    if (verifyEmailBtn) {
      setButtonState(verifyEmailBtn, !emailChanged && !isVerifiedEmail && currentEmail.length > 0);
    }
  };

  const disableOnSubmit = button => {
    if (!button) return;
    button.addEventListener('click', function () {
      setTimeout(() => {
        button.disabled = true;
      }, 0);
    });
  };

  if (firstNameInput && lastNameInput && saveInfoBtn) {
    firstNameInput.addEventListener('input', checkPersonalInfoState);
    lastNameInput.addEventListener('input', checkPersonalInfoState);
    disableOnSubmit(saveInfoBtn);
    checkPersonalInfoState();
  }

  if (phoneInput && savePhoneBtn) {
    phoneInput.addEventListener('input', checkPhoneState);
    disableOnSubmit(savePhoneBtn);
    checkPhoneState();
  }

  if (emailInput && updateEmailBtn) {
    emailInput.addEventListener('input', checkEmailState);
    if (verifyEmailBtn) {
      disableOnSubmit(verifyEmailBtn);
    }
    disableOnSubmit(updateEmailBtn);
    checkEmailState();
  }

  const profileImageInput = document.getElementById('profileImage');
  const uploadNotification = document.getElementById('uploadNotification');
  const MAX_AVATAR_BYTES = 5 * 1024 * 1024; // 5MB

  const showUploadNotification = message => {
    if (!uploadNotification) {
      alert(message);
      return;
    }
    uploadNotification.textContent = message;
    uploadNotification.classList.remove('hidden');
    uploadNotification.classList.add('profile-upload-notification--visible');
    setTimeout(() => {
      uploadNotification.classList.remove('profile-upload-notification--visible');
      uploadNotification.classList.add('hidden');
    }, 5000);
  };

  const clearUploadNotification = () => {
    if (!uploadNotification) return;
    uploadNotification.textContent = '';
    uploadNotification.classList.add('hidden');
    uploadNotification.classList.remove('profile-upload-notification--visible');
  };

  const getExtension = file => {
    const match = file.name.match(/\.([a-zA-Z0-9]+)$/);
    if (match) return match[1].toLowerCase();
    const mimeMap = {
      'image/jpeg': 'jpg',
      'image/png': 'png',
      'image/webp': 'webp',
      'image/gif': 'gif'
    };
    return mimeMap[file.type] || 'png';
  };

  const uploadToMinio = async (file, path) => {
    if (file.size > MAX_AVATAR_BYTES) {
      throw new Error(`Image too large. Maximum upload size is ${Math.round(MAX_AVATAR_BYTES / 1024 / 1024)} MB.`);
    }

    const formData = new FormData();
    formData.append('file', file);

    const res = await fetch(`/lib/services/upload.php?path=${encodeURIComponent(path)}&bucket=retrade`, {
      method: 'POST',
      body: formData
    });

    const raw = await res.text();
    let data;
    try {
      data = JSON.parse(raw);
    } catch (err) {
      throw new Error(`Upload failed: invalid server response (${res.status}) ${raw}`);
    }

    if (!res.ok) {
      throw new Error(data.details || data.error || `Upload failed (HTTP ${res.status})`);
    }

    return data.url;
  };

  const submitProfileImage = async url => {
    const formData = new FormData();
    formData.append('profile_image_form', '1');
    formData.append('profile_image_url', url);

    const res = await fetch(window.location.pathname, {
      method: 'POST',
      body: formData
    });

    if (!res.ok) {
      const text = await res.text();
      throw new Error(text || 'Profile image update failed');
    }

    return res;
  };

  const profileAvatarBtn = document.getElementById('profileAvatar');
  const editAvatarBtn = document.getElementById('editAvatarBtn');
  const modalEditAvatarBtn = document.getElementById('modalEditAvatarBtn');
  const avatarPreviewModal = document.getElementById('avatarPreviewModal');
  const closeAvatarModal = document.getElementById('avatarModalClose');
  const avatarModalBackdrop = document.getElementById('closeAvatarModal');

  const openAvatarModal = () => {
    if (!avatarPreviewModal) return;
    avatarPreviewModal.classList.remove('hidden');
    avatarPreviewModal.setAttribute('aria-hidden', 'false');
  };

  const closeAvatarPreview = () => {
    if (!avatarPreviewModal) return;
    avatarPreviewModal.classList.add('hidden');
    avatarPreviewModal.setAttribute('aria-hidden', 'true');
  };

  const handleFileUpload = async () => {
    const file = profileImageInput.files[0];
    if (!file) return;
    if (!window.USER_UID) {
      showUploadNotification('User not identified.');
      return;
    }

    if (modalEditAvatarBtn) {
      modalEditAvatarBtn.disabled = true;
      modalEditAvatarBtn.textContent = 'Uploading...';
    }

    try {
      const path = `uid.${window.USER_UID}`;
      const url = await uploadToMinio(file, path);
      await submitProfileImage(url);
      window.location.reload();
    } catch (error) {
      console.error(error);
      showUploadNotification(error.message || 'Upload failed');
      if (modalEditAvatarBtn) {
        modalEditAvatarBtn.disabled = false;
        modalEditAvatarBtn.textContent = 'Update profile picture';
      }
    }
  };

  if (profileAvatarBtn) {
    profileAvatarBtn.addEventListener('click', openAvatarModal);
  }

  if (editAvatarBtn) {
    editAvatarBtn.addEventListener('click', () => {
      profileImageInput.click();
    });
  }

  if (modalEditAvatarBtn) {
    modalEditAvatarBtn.addEventListener('click', () => {
      profileImageInput.click();
    });
  }

  if (closeAvatarModal) {
    closeAvatarModal.addEventListener('click', closeAvatarPreview);
  }

  if (avatarModalBackdrop) {
    avatarModalBackdrop.addEventListener('click', closeAvatarPreview);
  }

  if (profileImageInput) {
    profileImageInput.addEventListener('change', async () => {
      await handleFileUpload();
    });
  }
});

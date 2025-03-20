document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const menuToggle = document.querySelector(".menu-toggle")
  const navMenu = document.querySelector("nav ul")

  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("show")
    })
  }

  // Password strength meter
  const passwordInput = document.getElementById("password")
  const passwordStrength = document.getElementById("password-strength")

  if (passwordInput && passwordStrength) {
    passwordInput.addEventListener("input", () => {
      const password = passwordInput.value
      let strength = 0

      if (password.length >= 8) strength += 1
      if (password.match(/[a-z]+/)) strength += 1
      if (password.match(/[A-Z]+/)) strength += 1
      if (password.match(/[0-9]+/)) strength += 1
      if (password.match(/[^a-zA-Z0-9]+/)) strength += 1

      switch (strength) {
        case 0:
        case 1:
          passwordStrength.className = "password-strength weak"
          passwordStrength.textContent = "Weak"
          break
        case 2:
        case 3:
          passwordStrength.className = "password-strength medium"
          passwordStrength.textContent = "Medium"
          break
        case 4:
        case 5:
          passwordStrength.className = "password-strength strong"
          passwordStrength.textContent = "Strong"
          break
      }
    })
  }

  // Certificate QR code generator
  const generateQRButton = document.getElementById("generate-qr")
  const qrCodeContainer = document.getElementById("qr-code")

  if (generateQRButton && qrCodeContainer) {
    generateQRButton.addEventListener("click", function () {
      const certificateId = this.getAttribute("data-certificate-id")
      const verifyUrl = window.location.origin + "/verify.php?id=" + certificateId

      // Clear previous QR code
      qrCodeContainer.innerHTML = ""

      // Create QR code (this is a placeholder - you would use a QR code library)
      const qrImage = document.createElement("img")
      qrImage.src = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + encodeURIComponent(verifyUrl)
      qrImage.alt = "Certificate QR Code"
      qrCodeContainer.appendChild(qrImage)

      qrCodeContainer.style.display = "block"
    })
  }

  // Copy certificate link to clipboard
  const copyLinkButton = document.getElementById("copy-link")

  if (copyLinkButton) {
    copyLinkButton.addEventListener("click", function () {
      const certificateId = this.getAttribute("data-certificate-id")
      const verifyUrl = window.location.origin + "/verify.php?id=" + certificateId

      navigator.clipboard.writeText(verifyUrl).then(() => {
        // Show success message
        const tooltip = document.createElement("div")
        tooltip.className = "tooltip"
        tooltip.textContent = "Link copied!"
        document.body.appendChild(tooltip)

        setTimeout(() => {
          tooltip.remove()
        }, 2000)
      })
    })
  }

  // Certificate verification form validation
  const verifyForm = document.querySelector(".verify-section form")

  if (verifyForm) {
    verifyForm.addEventListener("submit", (e) => {
      const certificateIdInput = document.getElementById("certificate_id")

      if (!certificateIdInput.value.trim()) {
        e.preventDefault()

        // Show error message
        const errorMessage = document.createElement("div")
        errorMessage.className = "form-error"
        errorMessage.textContent = "Please enter a certificate ID"

        // Remove any existing error messages
        const existingError = certificateIdInput.parentNode.querySelector(".form-error")
        if (existingError) existingError.remove()

        certificateIdInput.parentNode.appendChild(errorMessage)
        certificateIdInput.focus()
      }
    })
  }

  // Animated counters for statistics
  const statValues = document.querySelectorAll(".stat-value")

  if (statValues.length > 0) {
    statValues.forEach((stat) => {
      const targetValue = Number.parseInt(stat.textContent)
      let currentValue = 0
      const duration = 2000 // 2 seconds
      const interval = 50 // Update every 50ms
      const steps = duration / interval
      const increment = targetValue / steps

      stat.textContent = "0"

      const counter = setInterval(() => {
        currentValue += increment

        if (currentValue >= targetValue) {
          clearInterval(counter)
          stat.textContent = targetValue
        } else {
          stat.textContent = Math.floor(currentValue)
        }
      }, interval)
    })
  }
})


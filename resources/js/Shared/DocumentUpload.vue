<template>
  <div class="space-y-6">
    <!-- Required documents list -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Required Documents</h3>
      <ul class="space-y-2">
        <li 
          v-for="doc in requiredDocs" 
          :key="doc.id"
          class="flex items-center justify-between p-3 border rounded-lg bg-gray-50"
        >
          <div>
            <p class="text-sm font-medium text-gray-900">{{ `${doc.name}(${doc?.isRequired ? 'Required' : 'Optional'})` }}</p>
            <p class="text-xs text-gray-500">{{ doc.description }}</p>
          </div>
          <div 
            class="w-5 h-5 rounded-full flex items-center justify-center"
            :class="isDocumentUploaded(doc.id) 
              ? 'bg-green-500 text-white' 
              : 'bg-gray-200 text-gray-500'"
          >
            <svg 
              v-if="isDocumentUploaded(doc.id)" 
              class="w-3 h-3" 
              fill="currentColor" 
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" 
                d="M16.707 5.293a1 1 0 010 1.414l-8 
                   8a1 1 0 01-1.414 0l-4-4a1 1 0 
                   011.414-1.414L8 12.586l7.293-7.293a1 
                   1 0 011.414 0z" 
                clip-rule="evenodd"/>
            </svg>
          </div>
        </li>
      </ul>
    </div>

    <!-- Upload area -->
    <div 
      class="p-6 border-2 border-dashed rounded-lg text-center bg-white hover:bg-gray-50 cursor-pointer"
      @dragover.prevent 
      @drop.prevent="handleDrop"
    >
      <div class="flex flex-col items-center space-y-2">
        <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" 
             viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" 
                d="M12 16v-8m0 0l-3 3m3-3l3 3m6 2v4a2 2 0 01-2 
                   2H6a2 2 0 01-2-2v-4m16-4l-4-4a2 2 0 
                   00-2-2H6a2 2 0 00-2 2v4" />
        </svg>
        <p class="text-sm text-gray-600">Upload Required Documents</p>
        <p class="text-xs text-gray-400">PDF, JPG, PNG (Max 5MB)</p>
        <input 
          type="file" 
          multiple 
          class="hidden" 
          ref="fileInput" 
          @change="handleFileSelect" 
          :accept="acceptedFileTypes"
        />
        <button 
          @click="$refs.fileInput.click()" 
          class="px-4 py-2 text-sm text-white bg-primary rounded-lg hover:bg-primary/60"
        >
          Choose Files
        </button>
      </div>
    </div>

    <!-- Uploaded files -->
    <div class="space-y-2">
      <div 
        v-for="file in uploadedFiles" 
        :key="file.id"
        class="flex items-center justify-between p-3 border rounded-lg bg-gray-50"
      >
        <div class="flex-1 truncate">
          <p class="text-sm text-gray-800 truncate">{{ file.name }}</p>
          <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
        </div>
        <div class="flex items-center space-x-3">
          <span v-if="file.status === 'uploading'" class="text-xs text-primary">Uploading...</span>
          <span v-else-if="file.status === 'uploaded'" class="text-xs text-green-600">Uploaded</span>
          <span v-else-if="file.status === 'error'" class="text-xs text-red-600">Failed</span>
          <button 
            @click="removeFile(file.id)" 
            class="text-xs text-red-500 hover:underline"
          >
            Remove
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "DocumentUploader",
  props: {
    requiredDocs: {
      type: Array,
    }
  },
  data() {
    return {
      uploadedFiles: [],
      acceptedFileTypes: ".pdf,.jpg,.jpeg,.png",
      a: 0 // counter for documentType
    }
  },
  methods: {
    handleDrop(e) {
      this.handleFiles(e.dataTransfer.files)
    },
    handleFileSelect(e) {
      this.handleFiles(e.target.files)
    },
    handleFiles(files) {
      Array.from(files).forEach(file => {
        if (this.validateFile(file)) {
            console.log(file)
          const fileObj = {
            id: Date.now() + Math.random(),
            name: file.name,
            size: file.size,
            type: file.type,
            file,
            status: "pending",
            progress: 0,
            documentType: this.a + 1  // will be mapped manually
          }
          this.uploadedFiles.push(fileObj)
          this.uploadFile(fileObj)
          this.a++ // increment after assigning
        }
      })
    },
    validateFile(file) {
      const maxSize = 5 * 1024 * 1024 // 5MB
      const validTypes = ["application/pdf", "image/jpeg", "image/png"]
      if (!validTypes.includes(file.type)) {
        alert("Invalid file type: " + file.name)
        return false
      }
      if (file.size > maxSize) {
        alert("File too large: " + file.name)
        return false
      }
      return true
    },
    uploadFile(fileObj) {
      fileObj.status = "uploading"
      const interval = setInterval(() => {
        if (fileObj.progress < 100) {
          fileObj.progress += 20
        } else {
          clearInterval(interval)
          fileObj.progress = 100
          fileObj.status = "uploaded"
          this.$emit("update:files", this.uploadedFiles)
        }
      }, 500)
    },
    formatFileSize(size) {
      const kb = size / 1024
      return kb < 1024 ? `${kb.toFixed(1)} KB` : `${(kb/1024).toFixed(1)} MB`
    },
    removeFile(id) {
      this.uploadedFiles = this.uploadedFiles.filter(f => f.id !== id)
      this.$emit("update:files", this.uploadedFiles)
    },
    isDocumentUploaded(docKey) {
      return this.uploadedFiles.some(f => {
        console.log(f.documentType , docKey);
        return f.documentType === docKey && f.status === "uploaded"
      })
    }
  }
}
</script>

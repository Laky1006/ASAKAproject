<template>
  <MainLayout :user="auth.user">
    <section class="max-w-6xl mx-auto py-10 px-6">
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <svg class="w-8 h-8 text-[#e4299c]" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
          </svg>
          <h1 class="text-3xl font-bold text-[#2D1810]">My Saved Services</h1>
        </div>

        <!-- Manage Folders Button -->
        <button
          @click="manageFoldersModal = true"
          class="px-4 py-2 bg-white/90 backdrop-blur-sm text-[#e4299c] border-2 border-[#e4299c] rounded-xl hover:bg-[#e4299c] hover:text-white shadow-lg transition-all duration-200 font-semibold flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Manage Folders
        </button>
      </div>

      <div v-if="Object.keys(groupedServices).length === 0" class="backdrop-blur-lg bg-white/80 border border-white/60 rounded-2xl p-8 text-center shadow-xl">
        <div class="text-[#6b5b73] text-lg font-body mb-4">
          You haven't saved any services yet.
        </div>
        <Link 
          href="/" 
          class="inline-block px-6 py-3 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white rounded-xl hover:shadow-lg transition-all duration-200 font-semibold"
        >
          Browse Services
        </Link>
      </div>

      <div v-else class="space-y-8">
        <!-- Loop through folders -->
        <div v-for="(services, folderName) in groupedServices" :key="folderName || 'no-folder'">
          <!-- Folder Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <svg class="w-6 h-6 text-[#e8662c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
              </svg>
              <h2 class="text-2xl font-bold text-[#2D1810]">
                {{ folderName || 'Unsorted' }}
              </h2>
              <span class="text-sm text-[#6b5b73] bg-[#e4299c]/10 px-3 py-1 rounded-full">
                {{ services.length }} {{ services.length === 1 ? 'service' : 'services' }}
              </span>
            </div>

            <!-- Folder Actions (only for named folders) -->
            <div v-if="folderName" class="flex gap-2">
              <button
                @click="showRenameModal(folderName)"
                class="p-2 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#e4299c]/10 rounded-lg transition-all"
                title="Rename folder"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
              </button>
              <button
                @click="showDeleteModal(folderName)"
                class="p-2 text-[#6b5b73] hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"
                title="Delete folder"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Services Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            <div
              v-for="savedService in services"
              :key="savedService.id"
              class="group relative overflow-hidden rounded-2xl backdrop-blur-lg bg-white/40 shadow-xl border border-white/60 hover:shadow-2xl hover:scale-105 transition-all duration-300"
            >
              <!-- Action Buttons (Top Right) -->
              <div class="absolute top-3 right-3 z-10 flex gap-2 opacity-100 group-hover:opacity-100 transition-opacity">
                <!-- Move Button -->
                <button
                  @click="moveService(savedService)"
                  class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-[#e4299c] border border-[#e4299c]/30 hover:bg-[#e4299c] hover:text-white flex items-center justify-center transition-all duration-200 shadow-lg"
                  title="Move to folder"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                  </svg>
                </button>
                
                <!-- Remove Button -->
                <button
                  @click="showRemoveModal(savedService)"
                  class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-red-500 border border-red-200 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all duration-200 shadow-lg"
                  title="Remove from saved"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              <!-- Clickable Link -->
              <Link
                :href="route('services.show', savedService.service.id)"
                class="block"
              >
                <!-- Image -->
                <div class="relative overflow-hidden">
                  <img
                    :src="savedService.service.banner ? `/storage/${savedService.service.banner}` : '/images/default-banner.jpg'"
                    alt="Service Banner"
                    class="w-full h-48 object-cover transition-transform duration-300"
                  />
                  <div class="absolute inset-0 bg-gradient-to-br from-[#e4299c]/30 to-[#e8662c]/30 mix-blend-multiply"></div>
                </div>

                <!-- Content -->
                <div class="p-5">
                  <h3 class="text-lg font-bold mb-2 text-[#2D1810] line-clamp-2">{{ savedService.service.title }}</h3>
                  <p class="text-sm text-[#6b5b73] mb-2 font-body">
                    Provider: <span class="font-semibold">{{ savedService.service.provider?.user?.name || 'Unknown' }}</span>
                  </p>

                  <!-- Rating -->
                  <div class="flex items-center text-sm mb-2">
                    <div class="flex">
                      <span
                        v-for="n in 5"
                        :key="n"
                        class="text-base"
                        :class="{
                          'text-yellow-500': (savedService.service.rating || 0) >= n,
                          'text-gray-300': (savedService.service.rating || 0) < n
                        }"
                      >★</span>
                    </div>
                    <span class="ml-2 text-[#6b5b73] font-body text-xs">
                      {{ savedService.service.rating ? `${savedService.service.rating}/5` : 'Not rated' }}
                    </span>
                  </div>

                  <!-- Price -->
                  <p class="text-sm text-[#e8662c] font-bold mb-3 font-body">
                    € {{ savedService.service.price ?? 'Free' }}
                  </p>

                  <!-- Saved Date -->
                  <div class="pt-3 border-t border-white/40">
                    <p class="text-xs text-[#6b5b73] font-body">
                      Saved {{ formatDate(savedService.created_at) }}
                    </p>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Manage Folders Modal -->
    <Transition name="fade">
      <div v-if="manageFoldersModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="backdrop-blur-xl bg-white/90 rounded-2xl p-6 w-full max-w-md shadow-2xl border border-white/60">
          <h2 class="text-xl font-bold mb-4 text-[#2D1810]">Manage Folders</h2>
          
          <!-- Create New Folder -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[#2D1810] mb-2">Create New Folder:</label>
            <div class="flex gap-2">
              <input
                v-model="newFolderName"
                type="text"
                placeholder="Folder name"
                class="flex-1 px-3 py-2 border border-white/60 rounded-xl backdrop-blur-sm bg-white/70 focus:outline-none focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] text-[#2D1810]"
                @keyup.enter="createFolder"
              />
              <button
                @click="createFolder"
                :disabled="!newFolderName.trim()"
                class="px-4 py-2 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white rounded-xl hover:shadow-lg transition-all duration-200 font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Create
              </button>
            </div>
          </div>

          <!-- Existing Folders -->
          <div v-if="folderNames && folderNames.length > 0" class="mb-6">
            <label class="block text-sm font-medium text-[#2D1810] mb-2">Existing Folders:</label>
            <div class="space-y-2 max-h-48 overflow-y-auto">
              <div
                v-for="folder in folderNames"
                :key="folder"
                class="flex items-center justify-between p-3 backdrop-blur-sm bg-white/60 border border-white/60 rounded-xl"
              >
                <span class="font-medium text-[#2D1810]">{{ folder }}</span>
                <div class="flex gap-2">
                  <button
                    @click="showRenameModal(folder)"
                    class="p-1 text-[#e4299c] hover:text-[#ff6b9d] transition-colors"
                    title="Rename"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                  </button>
                  <button
                    @click="showDeleteModal(folder)"
                    class="p-1 text-red-500 hover:text-red-700 transition-colors"
                    title="Delete"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end">
            <button
              @click="manageFoldersModal = false"
              class="px-5 py-2 rounded-xl backdrop-blur-sm bg-white/70 border border-white/60 hover:bg-white/90 transition-all duration-200 font-semibold text-sm text-[#2D1810]"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Rename Folder Modal -->
    <Transition name="fade">
      <div v-if="showRenameInput" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="backdrop-blur-xl bg-white/90 rounded-2xl p-6 w-full max-w-sm shadow-2xl border border-white/60">
          <h2 class="text-xl font-bold mb-3 text-[#2D1810]">Rename Folder</h2>
          <p class="text-[#6b5b73] mb-4 font-body">Enter new name for "{{ folderToRename }}":</p>
          
          <input
            v-model="newFolderName"
            type="text"
            :placeholder="folderToRename"
            class="w-full px-3 py-2 border border-white/60 rounded-xl backdrop-blur-sm bg-white/70 focus:outline-none focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] text-[#2D1810] mb-4"
            @keyup.enter="confirmRename"
            @keyup.escape="cancelRename"
            ref="renameInput"
          />

          <div class="flex flex-col sm:flex-row justify-end gap-3">
            <button
              @click="cancelRename"
              class="px-5 py-2 rounded-xl backdrop-blur-sm bg-white/70 border border-white/60 hover:bg-white/90 transition-all duration-200 font-semibold text-sm text-[#2D1810]"
            >
              Cancel
            </button>
            <button
              @click="confirmRename"
              :disabled="!newFolderName.trim() || newFolderName.trim() === folderToRename"
              class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white hover:shadow-lg transition-all duration-200 font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Rename
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Move Service Modal -->
    <Transition name="fade">
      <div v-if="moveServiceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="backdrop-blur-xl bg-white/90 rounded-2xl p-6 w-full max-w-sm shadow-2xl border border-white/60">
          <h2 class="text-xl font-bold mb-3 text-[#2D1810]">Move Service</h2>
          <p class="text-[#6b5b73] mb-4 font-body leading-relaxed">
            Move "{{ serviceToMove?.service?.title }}" to:
          </p>
          
          <select 
            v-model="selectedFolder" 
            class="w-full px-3 py-2 border border-white/60 rounded-xl backdrop-blur-sm bg-white/70 focus:outline-none focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] text-[#2D1810] mb-4"
          >
            <option :value="null">Unsorted</option>
            <option v-for="folder in folderNames" :key="folder" :value="folder">
              {{ folder }}
            </option>
          </select>

          <div class="flex flex-col sm:flex-row justify-end gap-3">
            <button
              @click="moveServiceModal = false"
              class="px-5 py-2 rounded-xl backdrop-blur-sm bg-white/70 border border-white/60 hover:bg-white/90 transition-all duration-200 font-semibold text-sm text-[#2D1810]"
            >
              Cancel
            </button>
            <button
              @click="confirmMoveService"
              class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white hover:shadow-lg transition-all duration-200 font-semibold text-sm"
            >
              Move
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- PopupConfirm Modals -->
    <PopupConfirm
      v-model:show="showDeleteConfirm"
      title="Delete Folder"
      :message="`Delete '${folderToDelete}' folder? Services will be moved to unsorted.`"
      @confirm="confirmDelete"
    />

    <PopupConfirm
      v-model:show="showRemoveConfirm"
      title="Remove Service"
      :message="`Remove '${serviceToRemove?.service?.title}' from saved services?`"
      @confirm="confirmRemove"
    />
  </MainLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import PopupConfirm from '@/Components/PopupConfirm.vue'

const props = defineProps({
  auth: Object,
  groupedServices: Object,
  folderNames: { type: Array, default: () => [] },
})

// Modal states
const manageFoldersModal = ref(false)
const moveServiceModal = ref(false)
const showDeleteConfirm = ref(false)
const showRemoveConfirm = ref(false)
const showRenameInput = ref(false)

// Data states
const newFolderName = ref('')
const serviceToMove = ref(null)
const serviceToRemove = ref(null)
const folderToDelete = ref('')
const folderToRename = ref('')
const selectedFolder = ref(null)

// References
const renameInput = ref(null)

function createFolder() {
  if (!newFolderName.value.trim()) return

  router.post(route('saved-services.folders.create'), {
    folder_name: newFolderName.value.trim()
  }, {
    preserveScroll: true,
    onSuccess: () => {
      newFolderName.value = ''
    }
  })
}

function showRenameModal(folderName) {
  folderToRename.value = folderName
  newFolderName.value = folderName
  showRenameInput.value = true
  manageFoldersModal.value = false
  
  nextTick(() => {
    renameInput.value?.focus()
    renameInput.value?.select()
  })
}

function cancelRename() {
  showRenameInput.value = false
  manageFoldersModal.value = true
  folderToRename.value = ''
  newFolderName.value = ''
}

function confirmRename() {
  if (!newFolderName.value.trim() || newFolderName.value.trim() === folderToRename.value) return

  router.patch(route('saved-services.folders.rename'), {
    old_name: folderToRename.value,
    new_name: newFolderName.value.trim()
  }, {
    onSuccess: () => {
      showRenameInput.value = false
      folderToRename.value = ''
      newFolderName.value = ''
      window.location.reload()
    }
  })
}

function showDeleteModal(folderName) {
  folderToDelete.value = folderName
  showDeleteConfirm.value = true
}

function confirmDelete() {
  router.delete(route('saved-services.folders.delete'), {
    data: { folder_name: folderToDelete.value }
  }, {
    onSuccess: () => {
      folderToDelete.value = ''
      window.location.reload()
    }
  })
}

function showRemoveModal(savedService) {
  serviceToRemove.value = savedService
  showRemoveConfirm.value = true
}

function confirmRemove() {
  router.delete(route('saved-services.destroy'), {
    data: { service_id: serviceToRemove.value.service.id },
    preserveScroll: true,
    onSuccess: () => {
      serviceToRemove.value = null
    }
  })
}

function moveService(savedService) {
  serviceToMove.value = savedService
  selectedFolder.value = savedService.folder_name
  moveServiceModal.value = true
}

function confirmMoveService() {
  if (!serviceToMove.value) return

  router.patch(route('saved-services.move'), {
    service_id: serviceToMove.value.service.id,
    folder_name: selectedFolder.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      moveServiceModal.value = false
      serviceToMove.value = null
    }
  })
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return 'today'
  if (diffDays <= 7) return `${diffDays} days ago`
  if (diffDays <= 30) return `${Math.ceil(diffDays / 7)} weeks ago`
  return date.toLocaleDateString()
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

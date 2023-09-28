<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import CheckDecagram from 'vue-material-design-icons/CheckDecagram.vue'
import axios from 'axios';
const router = useRouter();
const token = localStorage.getItem('authToken');
const props = defineProps({ comment: Object, userId: Number });
const userId = props.userId
const comment = props.comment
let openOptions = ref(false);
const toggleOpenOptions = () => {
    openOptions.value = !openOptions.value;
}
const handleDelete = () => {
    axios.delete(`http://localhost:8000/api/comment/${comment.id}`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      params: {
        user_id: userId,
        comment_id: comment.id,
      },
    }).then((res)=>{
        console.log(res)
        window.location.reload();
    }).catch((err)=>{
        console.log(err)
    })
}
</script>
<template>
    <div>
      <div class="font-extrabold flex items-center justify-between mt-0.5 mb-1.5 w-full">
        <div class="flex items-center w-full">
          <div class="min-w-[60px]">
            <img v-if="comment.profile.picture" :src="comment.profile.picture" width="50" class="rounded-full m-2 mt-3">
            <img v-else src="../assets/pic.png" width="50" class="rounded-full m-2 mt-3">
          </div>
          <div class="flex text-white">
            {{ comment.profile.nickname }}
            <CheckDecagram v-if="comment.profile.verified" fillColor="#1DA1F2" :size="18"/>
          </div>
          <span class="font-[300] text-[15px] text-gray-500 pl-2">@{{ comment.user.name }}</span>
        </div>
        <div v-if="userId == comment.user_id" class="hover:bg-gray-400 rounded-full cursor-pointer relative text-white justify-end">
          <button type="button" class="block p-2">
            <DotsHorizontal @click="toggleOpenOptions"/>
          </button>
          <div v-if="openOptions" class="absolute mt-1 p-3 right-0 w-[300px] bg-black border border-gray-700 rounded-lg shadow-lg">
            <div class="flex items-center cursosr-pointer" @click="handleDelete">
              <TrashCanOutline class="pr-3" fillColor="#DC2626" :size="18"/>
              <span class="text-red-600 font-extrabold">Delete</span>
            </div>
          </div>
        </div>
      </div>
      <div class="w-full border-b border-gray-300 mt-2 text-white">
        <div class="p-2 w-full">
          <div class="pb-3">{{ comment.text }}</div>
          <div v-if="comment.media">
            <div v-if="comment.fileFormat !== 'mp4'" class="rounded-xl">
              <img :src="comment.media" class="mt-2 object-fill rounded-xl w-full">
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
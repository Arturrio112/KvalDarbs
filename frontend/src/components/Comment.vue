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
onMounted(()=>{
    if(comment.fileName){
        axios.get('http://localhost:8000/api/file',{
            headers:{
                Authorization: `Bearer ${token}`,
            },
            params:{
                fileName: comment.fileName
            }
        }).then((res)=>{
            comment.media = res.data.data.media
        }).catch((err)=>{
            console.log(err);
        })
    }
    const mentionRegex = /@([a-zA-Z0-9_]+)/g;
    const mentions = comment.text.match(mentionRegex);

    if (mentions) {
        // Extract usernames from mentions (remove @ symbol)
        const usernames = mentions.map(mention => mention.substring(1));

        // Fetch mentioned users' data from the backend
        axios.get('http://localhost:8000/api/get/mention', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            params: {
                usernames: usernames.join(','), // Send usernames as a comma-separated string
            }
        }).then((res) => {
            const mentionedUsers = res.data.data.users;
            
            // Iterate through mentioned users and replace mentions with clickable links
            mentionedUsers.forEach(user => {
                const mentionRegex = new RegExp(`@${user.name}`, 'g');
                comment.text = comment.text.replace(mentionRegex, `<a href="/user/${user.id}" style="color: #fca521">@${user.name}</a>`);
            });


            // Handle the mentioned users as needed (e.g., display in the UI)
        }).catch((err) => {
            console.log(err);
        });
    }
})
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
function isValidHexColor(color) {
    const hexColorRegex = /^#[0-9A-Fa-f]{6}$/;
    return hexColorRegex.test(color);
}
const handleProfile = ()=>{
    console.log("clicked on profile")
    router.push({name: 'profile', params: {id: comment.user_id}})
}
</script>
<template>
    <div>
      <div class="font-extrabold flex items-center justify-between mt-0.5 mb-1.5 w-full">
        <div class="flex items-center w-full">
          <div class="min-w-[60px]">
            <img v-if="comment.profile.picture" :src="comment.profile.picture" class="w-[50px] h-[50px] rounded-full object-cover mt-3 ml-2 mr-2" :style="{ borderColor: `${comment.profile.borderColor}`, borderWidth: '2px', borderStyle: 'solid' }">
            <img v-else src="../assets/pic.png" class="w-[50px] h-[50px] rounded-full object-cover mt-3 ml-2 mr-2">
          </div>
          <div class="flex text-white cursor-pointer" @click="handleProfile">
            <span v-if="comment.profile.fontColor" :style="{ color: isValidHexColor(comment.profile.fontColor) ? comment.profile.fontColor : '#000000' }">
              {{ comment.profile.nickname }}
            </span>
            <span v-else>
              {{ comment.profile.nickname }}
            </span>
            <CheckDecagram v-if="comment.profile.verified" fillColor="#fca521" :size="18"/>
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
          <div class="pb-3" v-html="comment.text"></div>
          <div v-if="comment.media">
            <div v-if="comment.fileFormat !== 'mp4'" class="rounded-xl">
              <img :src="comment.media" class="mt-2 object-fill rounded-xl w-full">
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
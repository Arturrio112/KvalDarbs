<script setup>

import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
//import { Link } from '@inertiajs/vue3';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue'
import ChartBar from 'vue-material-design-icons/ChartBar.vue'
import MessageOutline from 'vue-material-design-icons/MessageOutline.vue'
import Sync from 'vue-material-design-icons/Sync.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import CheckDecagram from 'vue-material-design-icons/CheckDecagram.vue'
import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';
import Share from 'vue-material-design-icons/Share.vue';
import FileGifBox from 'vue-material-design-icons/FileGifBox.vue';
import Emoticon from 'vue-material-design-icons/Emoticon.vue';
import Comment from '../components/Comment.vue'
const props = defineProps({ post: Object, userId: Number });
const router = useRouter();
const userId = props.userId
const post = props.post
let openOptions = ref(false);
let seePost = ref(false)
let commentInput = ref('')
let commentArea = ref(null)
let showUpload = ref('')
let uploadType = ref('')
let file=ref('')
const token = localStorage.getItem('authToken');
const isLikedByUser = computed(() => {
  return post.like.some((like) =>  like ? like.user_id === userId : false);
});
onMounted(()=>{
    if(post.fileName){
        axios.get('http://localhost:8000/api/file',{
            headers:{
                Authorization: `Bearer ${token}`,
            },
            params:{
                fileName: post.fileName
            }
        }).then((res)=>{
            post.media = res.data.data.media
        }).catch((err)=>{
            console.log(err);
        })
    }
})
const handleLike = ()=>{
    console.log('clicked')
    if (isLikedByUser.value) {
    console.log(post.id)
    axios
        .delete(`http://localhost:8000/api/post/${post.id}/like`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
        data: {
            post_id: post.id,
            user_id: userId,
        },
        })
        .then((res) => {
            console.log(res);
            const userIdIndex = post.like.findIndex((like) => like.user_id === userId);
            if (userIdIndex !== -1) {
                post.like.splice(userIdIndex, 1);
            }
            post.statistic.like -=1
            })
        .catch((err) => {
        console.log(err);
        });
    } else {
    console.log(post.id)
    axios
        .post(`http://localhost:8000/api/post/${post.id}/like`, {
        post_id: post.id,
        user_id: userId,
        }, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
        })
        .then((res) => {
            console.log(res);
            post.like.push(res.data.data.like);
            post.statistic.like +=1
        })
        .catch((err) => {
        console.log(err);
        });
    }
}
const getFile = (e)=>{
    const selectedFile = e.target.files[0];
    
    if (selectedFile) {
        const allowedFormats = ["jpeg", "jpg", "png", "mp4", "gif"];
        const fileExtension = selectedFile.name.split('.').pop().toLowerCase();

        if (allowedFormats.includes(fileExtension)) {
            file.value = selectedFile;
            showUpload.value = URL.createObjectURL(selectedFile);
            uploadType.value = fileExtension;
        } else {
            // Display an error to the user, indicating invalid file format
            alert("Invalid file format. Allowed formats: jpeg, png, mp4, gif.");
            // Clear the input element
            e.target.value = null;
            // Reset values to indicate no selected media
            file.value = null;
            showUpload.value = '';
            uploadType.value = '';
        }
    }
  }
const handleDelete = () => {
    console.log('clicked on delete')
    console.log(post.id,post.user_id, userId)
    console.log(token)
    axios.delete(`http://localhost:8000/api/post/${post.id}`, {
        data: {
            post_id: post.id,
            user_id: userId,
        },
        headers: {
            Authorization: `Bearer ${token}`,
        },
    }).then((res)=>{
        console.log(res);
        window.location.reload();
    }).catch((err)=>{
        console.log(err);
    })
}
const handleRepost = ()=>{
    console.log('clicked on repost')
    console.log(post.id,post.user_id, userId)
    axios.post(`http://localhost:8000/api/post/${post.id}/repost`,{
            post_id: post.id,
            user_id: userId,
        }, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    }).then((res)=>{
        console.log(res);
        post.statistic.repost +=1
    }).catch((err)=>{
        console.log(err);
    })
}
const handleComment = ()=>{
    const formData = new FormData()
    formData.append('text', commentInput.value);
    formData.append('user_id', userId);
    formData.append('post_id', post.id)
    if (file.value) {
        formData.append('media', file.value);
        formData.append('fileFormat', uploadType.value);
    } else {
        formData.append('media', ''); // No media, so send an empty value
        formData.append('fileFormat', '');
    }
    
    console.log(formData);
    axios.post(`http://localhost:8000/api/post/${post.id}/comment`, formData,{
        headers: {
            Authorization: `Bearer ${token}`
        }
    }).then((res)=>{
        console.log(res)
        window.location.reload();
    }).catch((err)=>{
        console.log(err)
    })
}
const heartColor = computed(()=>{
    if(isLikedByUser.value){
        return '#FF0000'
    }else{
        return '#5e5c5c'
    }
})
const togglePostBox = ()=>{
    console.log("clicked")
    console.log(seePost.value)
    seePost.value = !seePost.value;
    showUpload.value=''
    uploadType.value=''
    commentInput.value=''
}
const toggleOpenOptions = () => {
    seePost.value = false
    openOptions.value = !openOptions.value;
}
const commentTextareaInput = (e)=>{
    commentArea.value.style.height = "auto"
    commentArea.value.style.height = `${e.target.scrollHeight}px`
}
const handleProfile = ()=>{
    console.log("clicked on profile")
    router.push({name: 'profile', params: {id: post.user_id}})
}

</script>

<template>
        <div class="w-full border-b border-b-gray-800 mt-2 " >
            <div class="p-2 w-full">
                <div class="
                    font-extrabold
                    flex
                    items-center
                    justify-between
                    mt-0.5
                    mb-1.5
                ">
                    <div class="flex items-center">
                        <div class="min-w-[60px]">
                            <img v-if="post.profile.picture!=null" :src="post.profile.picture" width="50" class="rounded-full m-2 mt-3">
                            <img v-else src="../assets/pic.png" width="50" class="rounded-full m-2 mt-3">
                        </div>
                        <div class="flex cursor-pointer" @click="handleProfile">
                            {{ post.profile.nickname }}
                            <CheckDecagram v-if="post.profile.verified" fillColor="#1DA1F2" :size="18"/>
                        </div>
                        <span class="
                            font-[300]
                            text-[15px]
                            text-gray-500
                            pl-2
                        ">@{{ post.user.name }}</span>
                    </div>
                    <div  class="
                        hover:bg-gray-400
                        rounded-full
                        cursor-pointer
                        relative
                    ">
                        <button type="button" class="block p-2">
                            <DotsHorizontal @click="toggleOpenOptions"/>
                        </button>
                        <div
                            v-if="openOptions"
                            class="
                                absolute
                                mt-1
                                p-3
                                right-0
                                w-[300px]
                                bg-black
                                border
                                border-gray-700
                                rounded-lg
                                shadow-lg
                        ">
                            <div
                                v-if="post.user_id == userId"
                                class="
                                    flex
                                    items-center
                                    cursosr-pointer
                                "
                                @click="handleDelete"
                            >
                                <TrashCanOutline class="pr-3" fillColor="#DC2626" :size="18"/>
                                <span class="text-red-600 font-extrabold">Delete</span>
                            </div>
                            <div v-if="post.user_id != userId" @click="handleRepost" class="
                                flex
                                items-center
                                cursor-pointer
                            ">
                                <Sync class="pr-3" fillColor="white" :size="18"/>
                                <span class="font-extrabold text-white">Repost</span>
                            </div>
                            <div  @click="handleShare" class="
                                flex
                                items-center
                                cursor-pointer
                            ">
                                <Share class="pr-3" fillColor="white" :size="18"/>
                                <span class="font-extrabold text-white">Share</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-3 cursor-pointer" @click="togglePostBox">{{ post.text }}</div>
                <div v-if="post.media">
                    <div v-if="post.fileFormat !== 'mp4'" class="rounded-xl">
                        <img :src="post.media" class="mt-2 object-fill rounded-xl w-full">
                    </div>
                    <div v-else>
                        <video :src="post.media" controls class="rounded-xl"></video>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 w-4/5">
                    <div class="flex">
                        <MessageOutline fillColor="#5e5c5c" :size="18"/>
                        <span v-if="post.statistic"
                            class="text-xs font-extrabold text-[#5e5c5c] ml-3"
                        >{{ post.statistic.comment }}</span>
                    </div>
                    <div class="flex">
                        <Sync fillColor="#5e5c5c" :size="18"/>
                        <span v-if="post.statistic"
                            class="text-xs font-extrabold text-[#5e5c5c] ml-3"
                        >{{ post.statistic.repost }}</span>
                    </div>
                    <div class="flex cursor-pointer">
                        <HeartOutline @click="handleLike" :fillColor="heartColor" :size="18"/>
                        <span v-if="post.statistic"
                            class="text-xs font-extrabold text-[#5e5c5c] ml-3"
                        >{{ post.statistic.like }}</span>
                    </div>
                    <div class="flex">
                        <ChartBar fillColor="#5e5c5c" :size="18"/>
                        <span v-if="post.statistic"
                            class="text-xs font-extrabold text-[#5e5c5c] ml-3"
                        >{{ post.statistic.views }}</span>
                    </div>
                </div>
            </div>
            <div id="postOverlay" v-if="seePost == true" class="
                fixed
                inset-0
                z-50
                flex
                items-center
                justify-center
                bg-black
                md:bg-gray-100
                md:bg-opacity-80
            ">
                <div class="
                    w-[calc(100%-100px)]
                    md:max-w-2xl
                    md:mx-auto
                    md:mt-30
                    md:rounded-xl
                    bg-black
                ">
                    <div class="
                        w-full
                        flex
                        items-center
                        justify-between
                        md:inline-block
                        p-2
                        m-2
                        rounded-full
                        cursor-pointer
                    ">
                        <div @click="togglePostBox" class="
                            hover:bg-gray-800
                            inline-block
                            p-2
                            rounded-full
                            cursor-pointer
                        ">
                            <Close fillColor="#FFFFFF" :size="28" class="md:block hidden"/>
                            <ArrowLeft fillColor="#FFFFFF" :size="28" class="md:hidden block"/>
                        </div>
                    </div>
                    <div class="w-full flex">
                        <div class="ml-3.5 mr-2">
                            <img v-if="post.profile.picture" :src="post.profile.picture" width="55" class="rounded-full " >
                            <img v-else src="../assets/pic.png" class="rounded-full" width="55">
                        </div>
                        <div class="w-[calc(100%-100px)]">
                            <div class="inline-block">
                                <div class="
                                    flex
                                    items-center
                                    text-white
                                ">
                                    <div class="flex">
                                        {{ post.profile.nickname }}
                                        <CheckDecagram v-if="post.profile.verified" fillColor="#1DA1F2" :size="18"/>
                                    </div>
                                    <span class="
                                        font-[300]
                                        text-[15px]
                                        text-gray-500
                                        pl-2
                                    ">@{{ post.user.name }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="p-4 text-white">
                                    {{ post.text }}
                                </div>
                            </div>
                            <div v-if="post.media">
                                <div v-if="post.fileFormat !== 'mp4'" class="rounded-xl">
                                    <img :src="post.media" class="mt-2 object-fill rounded-xl w-full">
                                </div>
                                <div v-else>
                                    <video :src="post.media" controls class="rounded-xl"></video>
                                </div>
                            </div>
                            
                            <div class=" flex items-center justify-between p-4">
                                <div class="w-full flex justify-between">
                                    
                                    <div class="flex items-center mr-3">
                                        <MessageOutline fillColor="#5e5c5c" :size="18"/>
                                        <span v-if="post.statistic" class="text-xs font-extrabold text-[#5e5c5c] ml-1">
                                            {{ post.statistic.comment }}
                                        </span>
                                    </div>
                                    <div class="flex items-center mr-3">
                                        <Sync fillColor="#5e5c5c" :size="18"/>
                                        <span v-if="post.statistic" class="text-xs font-extrabold text-[#5e5c5c] ml-1">
                                            {{ post.statistic.repost }}
                                        </span>
                                    </div>
                                    <div class="flex items-center mr-3 cursor-pointer">
                                        <HeartOutline @click="handleLike" :fillColor="heartColor"  :size="18"/>
                                        <span v-if="post.statistic" class="text-xs font-extrabold text-[#5e5c5c] ml-1">
                                            {{ post.statistic.like }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <ChartBar fillColor="#5e5c5c" :size="18"/>
                                        <span v-if="post.statistic" class="text-xs font-extrabold text-[#5e5c5c] ml-1">
                                            {{ post.statistic.views }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full flex">
                        <div class="p-4 w-full">
                            <textarea
                                :oninput="commentTextareaInput"
                                v-model="commentInput"
                                rows="3"
                                ref="commentArea"
                                class="w-full
                                border-b-2
                                border-gray-400 
                                bg-black 
                                text-white
                                focus:ring-0
                                font-extrabold 
                                p-2"
                                placeholder="Write a comment..."
                            ></textarea>
                            <div class="w-full">
                                <video controls v-if="uploadType === 'mp4'" :src="showUpload" class="rounded-xl overflow-auto"/>
                                <img v-else :src="showUpload" class="rounded-xl min-w-full">
                            </div>
                            <div class="
                                flex
                                items-center
                                justify-between
                                py-2
                            ">
                                <div class="flex items-center">
                                    <img v-if="post.profile.picture" src="" width="55" class="rounded-full">
                                    <div class="
                                        hover:bg-gray-800
                                        inline-block
                                        p-2
                                        rounded-full
                                        cursor-pointer
                                    ">
                                        <label for="fileUpload" class="cursor-pointer">
                                            <ImageOutline fillColor="#1DA1F2" :size="25"/>
                                        </label>
                                        <input type="file" id="fileUpload" class="hidden" @change="getFile">
                                    </div>
                                    <div class="
                                        hover:bg-gray-800
                                        inline-block
                                        p-2
                                        rounded-full
                                        cursor-pointer
                                    ">
                                        <FileGifBox fillColor="#1DA1F2" :size="25"/>
                                    </div>
                                    <div class="
                                        hover:bg-gray-800
                                        inline-block
                                        p-2
                                        rounded-full
                                        cursor-pointer
                                    ">
                                        <Emoticon fillColor="#1DA1F2" :size="25"/>
                                    </div>
                                </div>
                                <button @click="handleComment" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Comment</button>
                            </div>
                        </div>
                    </div>
                    <div class="w-full border-t border-gray-300 mt-4"></div>
                    <div class="w-full flex flex-col overflow-auto" v-if="post.comment">
                        <div class="w-full" v-for="comment in post.comment" :key="comment.id">
                            <Comment :comment="comment" :userId="userId" />
                        </div>
                    </div>
                </div>
            </div>


        </div>
</template>
<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Sync from 'vue-material-design-icons/Sync.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Post from '../components/Post.vue'
const props = defineProps({ repost: Object, userId: Number });
const userId = props.userId
const repost = props.repost
const token = localStorage.getItem('authToken');
let openOptions = ref(false);
const toggleOpenOptions = () => {
    openOptions.value = !openOptions.value;
}
const handleDelete = () => {
    console.log('clicked on delete')
    console.log(token)
    axios.delete(`http://localhost:8000/api/repost/${repost.id}`, {
        data: {
            post_id: repost.post_id,
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
</script>
<template>
    <div class="w-full">
        <div class="flex justify-between mb-0">
            <div class="
                flex
                items-center
                cursor-pointer
                px-3
            ">
                <Sync class="pr-2" fillColor="gray" :size="18"/>
                <span class="font-extrabold text-gray">{{repost.user.profile.nickname}} reposted</span>
            </div>
            <div v-if="repost.user_id == userId" class="
                hover:bg-gray-400
                rounded-full
                relative
                mr-2
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
                        v-if="repost.user_id == userId"
                            class="
                                flex
                                items-center
                                cursor-pointer
                            "
                            @click="handleDelete"
                        >
                        <TrashCanOutline class="pr-3" fillColor="#DC2626" :size="18"/>
                        <span class="text-red-600 font-extrabold">Delete</span>
                    </div>
                </div>
            </div>
        </div>
        <div  class="mt-0 flex" :key="repost.post.id">
            <Post :post="repost.post" :userId="userId"/>
        </div>
    </div>
</template>
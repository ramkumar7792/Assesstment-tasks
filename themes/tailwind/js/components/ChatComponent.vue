<template>
    <div>

        <div class="flex items-center">
            <input
                type="text"
                v-model="newItem"
                @keyup.enter="addItem"
                placeholder="What do you need to do?"
                class="flex-1 px-2 py-1 border rounded-lg"
            />
            <button
                @click="addItem"
                class="px-4 py-1 ml-2 text-white bg-blue-500 rounded-lg"
            >
                Add
            </button>
        </div>
        <div class="flex flex-col justify-start h-80">
            <div ref="itemsContainer" class="p-4 overflow-y-auto max-h-fit">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class=" items-center mb-2"
                >

                    <div class="p-2 mr-auto bg-gray-200 rounded-lg">
                        <input type="radio"  class="p-2 mr-2" @click="deleteItem" :value="item.id">
                        {{ item.text }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import axios from "axios";
import { nextTick, onMounted, ref, watch } from "vue";

const props = defineProps({
    friend: {
        type: Object,
        required: true,
    },
    currentUser: {
        type: Object,
        required: true,
    },
});

const items = ref([]);
const newItem = ref("");
const itemsContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);

watch(
    items,
    () => {
        nextTick(() => {
            itemsContainer.value.scrollTo({
                top: itemsContainer.value.scrollHeight,
                behavior: "smooth",
            });
        });
    },
    { deep: true }
);

const addItem = () => {
    if (newItem.value.trim() !== "") {
        axios
            .post(`/items/${props.friend.id}`, {
                item: newItem.value,
            })
            .then((response) => {
                items.value.push(response.data);
                newItem.value = "";
            });
    }
};


const deleteItem = (e) => {
    e.target.parentNode.style.textDecoration = "line-through";
    axios
            .post(`/items/${props.friend.id}/`+e.target.value, {
                item: e.target.value,
            })
            .then((response) => {
                newItem.value = "";
            });
}

onMounted(() => {
    axios.get(`/items/${props.friend.id}`).then((response) => {
        console.log(response.data);
        items.value = response.data;
    });

    Echo.private(`chat.${props.currentUser.id}`)
        .listen("ItemSent", (response) => {
            items.value.push(response.item);
        })
        .listenForWhisper("typing", (response) => {
            isFriendTyping.value = response.userID === props.friend.id;

            if (isFriendTypingTimer.value) {
                clearTimeout(isFriendTypingTimer.value);
            }

            isFriendTypingTimer.value = setTimeout(() => {
                isFriendTyping.value = false;
            }, 1000);
        });
});
</script>

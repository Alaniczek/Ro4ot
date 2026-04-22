class ActiveRobots {
    jsonLocation;

    constructor(jsonLocation) {
        this.jsonLocation = jsonLocation;
    }

    async PrintRobots() {
        const container = document.querySelector(".ActiveRobotsSection");
        const response = await fetch(this.jsonLocation);
        const data = await response.json();

        container.innerHTML = "";

        const currentTime = new Date();
        for (const robot of data) {
            const card = document.createElement("div");
            card.classList.add("ActiveRobots");

            const lastStatusTime = new Date(robot.LastStatus);
            const diffInMinutes = (currentTime - lastStatusTime) / 1000 / 60;

            if (diffInMinutes < 1) {
                card.style.color = "green";
            } else {
                card.style.color = "red";
            }
            card.innerHTML = `
                <h3>${robot.Name}</h3>
                <p>${robot.IP}:${robot.Port}</p>
                <p>Model: ${robot.MODEL}</p>
                <p>Last log: ${robot.LastStatus}</p>
            `;

            container.appendChild(card);
        }
    }
}

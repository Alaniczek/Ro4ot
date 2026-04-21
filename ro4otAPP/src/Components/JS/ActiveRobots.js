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

        for (const robot of data) {
            const card = document.createElement("div");
            card.classList.add("ActiveRobots");
            if(robot.IsOnline === true) {
                card.style.color = "green";
            }else
                card.style.color = "red";

            card.innerHTML = `
                <h3>${robot.Name}</h3>
                <p>${robot.IP}:${robot.Port}</p>
                <p>Model: ${robot.MODEL}</p>
            `;

            container.appendChild(card);
        }
    }
}
